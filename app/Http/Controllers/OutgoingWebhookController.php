<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ohffs\MSTeamsAlerts\Facades\MSTeamsAlert;

class OutgoingWebhookController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'btext' => 'required_without_all:text,etext',
            'etext' => 'required_without_all:text,btext',
        ]);

        if ($validator->fails()) {
            return redirect()->route('message', [
                'message' => base64_encode('No message specified - no notification sent.'),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'c' => 'sometimes|required|string|exists:webhooks,shortcode',
        ]);
        if ($validator->fails()) {
            return redirect()->route('message', [
                'message' => base64_encode('Invalid channel - no notification sent.'),
            ]);
        }

        $webhook = $request->c ? Webhook::where('shortcode', $request->c)->first() : Webhook::where('is_default', true)->first();

        if (! $webhook) {
            return redirect()->route('message', [
                'message' => base64_encode('Invalid channel - no notification sent.'),
            ]);
        }

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

        // if there is a querystring parameter of `form=1` then we redirect them to a form so they can
        // supply thier own message
        if ($request->filled('form')) {
            return redirect()->route('form', ['btext' => base64_encode($message), 'c' => $webhook->shortcode]);
        }

        MSTeamsAlert::to($webhook->url)->message($message);

        $webhook->registerCalled();

        return redirect()->route('message', [
            'message' => base64_encode('Notification sent.'),
        ]);
    }
}
