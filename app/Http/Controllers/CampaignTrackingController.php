<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use App\Models\Newsletter;
use App\Services\Campaign\CampaignTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CampaignTrackingController extends Controller
{
    public function click(Request $request, string $token, CampaignTrackingService $tracking): RedirectResponse
    {
        $recipient = CampaignRecipient::where('token', $token)->firstOrFail();
        $tracking->captureClick($recipient, $request);

        $target = (string) $request->query('target', url('/'));
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $targetHost = parse_url($target, PHP_URL_HOST);
        if ($targetHost && $appHost && ! hash_equals((string) $appHost, (string) $targetHost)) {
            $target = url('/');
        }

        return redirect()->to($target);
    }

    public function open(Request $request, string $token, CampaignTrackingService $tracking): Response
    {
        $recipient = CampaignRecipient::where('token', $token)->firstOrFail();
        $tracking->captureOpen($recipient, $request);

        $pixel = base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

        return response($pixel, 200, ['Content-Type' => 'image/gif']);
    }

    public function unsubscribe(Request $request, string $token): RedirectResponse
    {
        $recipient = CampaignRecipient::where('token', $token)->firstOrFail();
        $recipient->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
        Newsletter::where('email', $recipient->email)->update(['status' => 'unsubscribed']);

        return redirect()->route('home')->with('success', 'You have been unsubscribed from campaign emails.');
    }
}
