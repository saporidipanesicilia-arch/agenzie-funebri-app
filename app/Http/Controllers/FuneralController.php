<?php

namespace App\Http\Controllers;

use App\Models\Funeral;
use Illuminate\Http\Request;

class FuneralController extends Controller
{
    public function index()
    {
        // Simple fetch for UI demonstration
        $funerals = Funeral::with('deceased')->latest()->paginate(10);
        return view('funerals.index', compact('funerals'));
    }

    public function show($id)
    {
        // Fetch to populate the UI
        $funeral = Funeral::with(['deceased', 'timeline', 'documents', 'activeQuote.items'])->findOrFail($id);
        return view('funerals.show', compact('funeral'));
    }

    public function create()
    {
        // Redirect to the wizard or show a simple form
        return redirect()->route('funerals.create-wizard');
    }

    public function store(Request $request)
    {
        // No business logic yet
    }

    public function edit(Funeral $funeral)
    {
        // No business logic yet
    }

    public function update(Request $request, Funeral $funeral)
    {
        // No business logic yet
    }

    public function destroy(Funeral $funeral)
    {
        // No business logic yet
    }
}
