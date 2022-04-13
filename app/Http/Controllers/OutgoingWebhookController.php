<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ohffs\MSTeamsAlerts\Facades\MSTeamsAlert;

class OutgoingWebhookController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required_without:btext',
            'btext' => 'required_without:text',
        ]);

        if ($validator->fails()) {
            return response()->view('message', [
                'message' => 'No message specified - no notification sent.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'c' => 'sometimes|required|string|exists:webhooks,shortcode',
        ]);
        if ($validator->fails()) {
            return response()->view('message', [
                'message' => 'Invalid channel - no notification sent.',
            ], 422);
        }

        $webhook = $request->c ? Webhook::where('shortcode', $request->c)->first() : Webhook::where('is_default', true)->first();

        if (! $webhook) {
            return response()->view('message', [
                'message' => 'Invalid channel - no notification sent.',
            ], 422);
        }

        MSTeamsAlert::to($webhook->url)->message($request->text ?? base64_decode($request->btext));

        $webhook->registerCalled();

        return response()->view('message', [
            'message' => 'Notification sent.',
        ]);
    }
}
