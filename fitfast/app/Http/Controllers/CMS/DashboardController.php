<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    function index()
    {
        return view('cms.pages.dashboard.index');
    }
}
