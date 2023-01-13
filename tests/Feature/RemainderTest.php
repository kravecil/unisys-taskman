<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Task;
use App\Models\User;
use App\Events\TaskRemainder;

class RemainderTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        // $response = $this->get('/');
        $user = User::find(4);
        $response = $this
            // ->actingAs($user)
            ->get('/');

        Task::lazy()
        ->whereNull('closed_at')
        ->each(function($item, $key) {
            // echo $item->body;
            if ($item->deadline_is_close > 0) TaskRemainder::dispatch($item);
            // echo $item->body;
        });

        $response->assertStatus(200);
    }
}
