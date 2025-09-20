<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->notifications
        ]);
    }


    public function markAsRead()
    {
        //
    }


    public function delete()
    {
        //
    }
}
