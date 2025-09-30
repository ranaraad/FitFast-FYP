<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use Illuminate\Http\Request;

class TipController extends Controller
{
    public function index()
    {
        $tips = Tip::orderBy('created_at', 'desc')->paginate(10);
        return view('cms.pages.tips.index', compact('tips'));
    }

    public function create()
    {
        return view('cms.pages.tips.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
        ]);

        Tip::create($validated);

        return redirect()->route('cms.tips.index')
            ->with('success', 'Tip created successfully.');
    }

    public function show(Tip $tip)
    {
        return view('cms.pages.tips.show', compact('tip'));
    }

    public function edit(Tip $tip)
    {
        return view('cms.pages.tips.edit', compact('tip'));
    }

    public function update(Request $request, Tip $tip)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
        ]);

        $tip->update($validated);

        return redirect()->route('cms.tips.index')
            ->with('success', 'Tip updated successfully.');
    }

    public function destroy(Tip $tip)
    {
        $tip->delete();

        return redirect()->route('cms.tips.index')
            ->with('success', 'Tip deleted successfully.');
    }
}
