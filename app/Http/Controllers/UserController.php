<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request) {
        $search = $request->input('search');
        return User::where('is_disabled', false)
            ->orderByDesc('is_leader')
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc')
            ->orderBy('middlename', 'asc')
            // ->where(function($query) use ($request, $search){
            //     if ($request->filled('search')) {
            //         $query->where('lastname', 'LIKE', '%' . $search . '%')
            //         ->orWhere('firstname', 'LIKE', '%' . $search . '%')
            //         ->orWhere('middlename', 'LIKE', '%' . $search . '%');
            //     }
            // })
            ->get()
            ->except([1])
            ->filter(function($value, $key) use ($request, $search) {
                if (!$request->filled('search')) return true;

                $searches = Str::of($search)->explode(' ');
                $results = [];

                foreach ($searches as $src) {
                    $results[] = Str::contains(Str::lower($value->lastname), Str::lower($src)) ||
                        Str::contains(Str::lower($value->firstname), Str::lower($src)) ||
                        Str::contains(Str::lower($value->middlename), Str::lower($src)) ||
                        Str::contains(Str::lower($value->department->number), Str::lower($src)) ||
                        Str::contains(Str::lower($value->department->title), Str::lower($src));
                }

                return collect($results)->every(function ($value, $key) { return $value; });
            })->values();
    }
}
