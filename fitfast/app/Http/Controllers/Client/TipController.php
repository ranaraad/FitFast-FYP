<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use Illuminate\Http\Request;

class TipController extends Controller
{
    public function index(Request $request)
    {
        $tips = Tip::orderBy('created_at', 'desc')->get();

        return view('client.tips.index', compact('tips'));
    }

    public function show(Tip $tip)
    {
        return view('client.tips.show', compact('tip'));
    }
}
