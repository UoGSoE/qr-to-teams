<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormController extends Controller
{
    public function create(Request $request)
    {
        $message = '';
        if ($request->filled('btext')) {
            $message = base64_decode($request->btext);
        }
        if ($request->filled('etext')) {
            try {
                $message = decrypt($request->etext);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                return redirect()->route('message', [
                    'message' => base64_encode('Invalid message - no notification sent.'),
                ]);
            }
        }

        if (! $message) {
            return redirect()->route('message', [
                'message' => base64_encode('No message specified - no notification sent.'),
            ]);
        }

        return view('form', [
            'message' => $message,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'message' => 'required',
            'c' => 'required',
        ]);

        if (! \Ldap::authenticate($request->username, $request->password)) {
            return redirect()->route('message', [
                'message' => base64_encode('You have entered an invalid GUID or password'),
            ]);
        }

        $user = \Ldap::findUser($request->username);
        if (! $user) {
            return redirect()->route('message', [
                'message' => base64_encode('You have entered an invalid GUID or password'),
            ]);
        }

        $message = "GUID : {$request->username} \n\n";
        $message .= "Name : {$user->forenames} {$user->surname} \n\n";
        $message .= "Email : {$user->email} \n\n";
        $message .= "Teams Chat : https://teams.microsoft.com/l/chat/0/0?users={$user->email} \n\n";
        $message .= "Message : {$request->message}";

        return redirect()->route('api.help', [
            'c' => $request->c,
            'etext' => encrypt($message),
        ]);
    }
}
