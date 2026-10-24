<?php

namespace App\Mail;

use App\Models\Inquiry;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Automatic reply to a visitor who left an email address, in the language they used on the site.
 */
class InquiryReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry)
    {
        $this->locale(in_array($inquiry->locale, array_keys(config('fdh.locales')), true) ? $inquiry->locale : 'bn');
    }

    public function envelope(): Envelope
    {
        $office = Setting::read('email', 'en');

        return new Envelope(
            subject: __('We received your request — :site', ['site' => Setting::read('site_name', $this->locale)]),
            replyTo: filter_var($office, FILTER_VALIDATE_EMAIL) ? [new Address($office, Setting::read('site_name', 'en'))] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.inquiry-received',
            with: [
                'lang' => $this->locale,
                'number' => 'FDH-'.str_pad((string) $this->inquiry->id, 5, '0', STR_PAD_LEFT),
            ],
        );
    }
}
