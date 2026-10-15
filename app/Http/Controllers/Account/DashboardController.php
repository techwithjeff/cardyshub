<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->latest()->take(5)->get();

        return view('account.dashboard', compact('orders'));
    }
}
