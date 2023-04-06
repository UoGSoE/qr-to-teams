<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function show(Request $request): View
    {
        return view('message', [
            'message' => base64_decode($request->message),
        ]);
    }
}
