<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Endpoints used by the new-inquiry notification email. Both routes require a valid signature,
 * so nobody can mark inquiries as read by guessing IDs.
 */
class InquiryEmailController extends Controller
{
    /**
     * Tracking pixel: marks the inquiry read when the email is opened with images enabled.
     */
    public function seen(Inquiry $inquiry): Response
    {
        $inquiry->markAsRead('email_open');

        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * "Open in admin" button: marks the inquiry read, then opens it in the admin panel (sign-in still required).
     */
    public function open(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->markAsRead('email_link');

        return redirect()->route('admin.inquiries', ['open' => $inquiry->id]);
    }
}
