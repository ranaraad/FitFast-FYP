<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index()
    {
        $faqs = FAQ::orderBy('created_at', 'desc')->get();
        return view('cms.pages.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('cms.pages.faqs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
        ]);

        FAQ::create($validated);

        return redirect()->route('cms.faqs.index') // Fixed route name
            ->with('success', 'FAQ created successfully.');
    }

    public function show(FAQ $faq)
    {
        return view('cms.pages.faqs.show', compact('faq'));
    }

    public function edit(FAQ $faq)
    {
        return view('cms.pages.faqs.edit', compact('faq'));
    }

    public function update(Request $request, FAQ $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
        ]);

        $faq->update($validated);

        return redirect()->route('cms.faqs.index') // Fixed route name
            ->with('success', 'FAQ updated successfully.');
    }

    public function destroy(FAQ $faq)
    {
        $faq->delete();

        return redirect()->route('cms.faqs.index') // Fixed route name
            ->with('success', 'FAQ deleted successfully.');
    }
}
