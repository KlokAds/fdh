<?php

namespace App\Http\Controllers;

use App\Support\MetaConversions;
use App\Support\MetaVisitor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * A visitor tapped Call, WhatsApp or Messenger. The pixel reports it from the browser; this sends
 * the same Contact event (same id) from the server, so it counts even when the pixel is blocked.
 */
class MetaContactController extends Controller
{
    public function __invoke(Request $request, MetaConversions $meta): Response
    {
        $data = $request->validate([
            'event_id' => ['required', 'string', 'regex:/^co-[a-z0-9]{20}$/'],
            'channel' => ['required', 'in:call,whatsapp,messenger'],
            'url' => ['required', 'string', 'max:2000'],
        ]);

        if ($meta->isConfigured() && is_site_url($data['url']) && ! MetaVisitor::isBot($request)) {
            $userData = MetaVisitor::userData($request);
            $events = [['name' => 'Contact', 'id' => $data['event_id'], 'custom' => ['content_name' => $data['channel'], 'content_category' => 'Contact']]];

            defer(fn () => $meta->track($userData, $data['url'], $events));
        }

        return response()->noContent();
    }
}
