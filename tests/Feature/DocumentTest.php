<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this
            ->actingAs(\App\Models\User::find(2))
            ->get('/');

        echo \App\Models\Document::find(12)
            ->tasks;

        // echo \App\Models\Document::find(12)
        //     ->tasks->each(function($item, $key) {
        //         $item->delete();
        //     });

        $response->assertStatus(200);
    }
}
