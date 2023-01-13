<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Partner;

class PartnerController extends Controller
{
    public function index(request $request) {
        // if (!$request->user()->can(['taskman_modify_documents'])) abort(403);
        return Partner::orderBy('name')->get();
    }

    public function store(Request $request) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort(403);

        $request->validate([
            'name' => ['required', 'string'],
            'inn' => ['nullable', 'string', 'max:12', 'regex:/^\d+$/'],
        ]);

        return Partner::create([
            'name' => $request->input('name'),
            'inn' => $request->input('inn'),
            'address' => $request->input('address'),
        ]);
    }

    public function update(Request $request, Partner $partner) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort(403);

        $request->validate([
            'name' => ['required', 'string'],
            'inn' => ['nullable', 'string', 'max:12', 'regex:/^\d+$/g'],
        ]);

        return $partner->update([
            'name' => $request->input('name'),
            'inn' => $request->input('inn'),
            'address' => $request->input('address'),
        ]);
    }
}
