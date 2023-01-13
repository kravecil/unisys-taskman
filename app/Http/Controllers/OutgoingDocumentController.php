<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OutgoingDocument;

class OutgoingDocumentController extends Controller
{
    public function index(Request $request) {
        if (!$request->user()->can(['taskman_register_outgoing']))
            abort(403, __('auth.cannot_register_outgoing'));

        return OutgoingDocument::latest()
            ->lazy()
            ->filter(function ($item, $key) use ($request) {
                if (!$request->filled('search')) return true;
                return
                    Str::contains(Str::lower($item->number), Str::lower($request->input('search'))) ||
                    Str::contains(Str::lower($item->description), Str::lower($request->input('search'))) ||
                    Str::contains(Str::lower($item->receiver), Str::lower($request->input('search')));
            })
            ->slice(($request->input('page', 1) - 1) * 10, 10)
            ->values();
    }

    public function store(Request $request) {
        if (!$request->user()->can(['taskman_register_outgoing']))
            abort(403, __('auth.cannot_register_outgoing'));

        $request->validate([
            'number' => ['required', 'numeric'],
            'signer_id' => ['nullable', 'numeric'],
            'executor_id' => ['nullable', 'numeric'],
        ]);

        $request->merge(['registrar_id' => $request->user()->id]);

        return OutgoingDocument::create($request->all())
        ->load('signer', 'executor');
    }

    public function destroy(Request $request, OutgoingDocument $outgoingDocument) {
        if (!$request->user()->can(['taskman_register_outgoing']))
            abort(403, __('auth.cannot_register_outgoing'));

        return $outgoingDocument->delete();
    }

    public function indexReceivers(Request $request) {
        return OutgoingDocument::where('receiver', 'like', '%' . $request->input('search', '') . '%')
            ->distinct()
            ->orderBy('receiver')
            ->pluck('receiver')
            ->take(10);
    }

    public function getLastNumber(Request $request) {
        if (!$request->user()->can(['taskman_register_outgoing']))
            abort(403, __('auth.cannot_register_outgoing'));

        return OutgoingDocument::getLastNumber();
    }
}
