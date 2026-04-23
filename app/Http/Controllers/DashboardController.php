<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;

class DashboardController
{
    public function home() {
        $links = Url::where('user_id', auth()->id())->latest()->get();
        return view('dashboard.home', compact('links'));
    }
}
