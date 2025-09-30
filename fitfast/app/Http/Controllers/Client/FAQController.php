<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index(Request $request)
    {
        $faqs = FAQ::orderBy('created_at', 'desc')->get();

        return view('client.faqs.index', compact('faqs'));
    }
}
