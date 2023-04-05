<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function show(Request $request): View
    {
        return view('message', [
            'message' => base64_decode($request->message),
        ]);
    }
}
