<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'message' => 'User logged in',
            'data' => $user
        ]);
    }
}
