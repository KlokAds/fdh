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
use Illuminate\Support\Facades\URL;

class NewInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New inquiry: '.$this->inquiry->name.($this->inquiry->service ? ' — '.$this->inquiry->service : ''),
            // Replying from the inbox answers the visitor directly.
            replyTo: $this->inquiry->email ? [new Address($this->inquiry->email, $this->inquiry->name)] : [],
        );
    }

    public function content(): Content
    {
        $digits = preg_replace('/\D/', '', $this->inquiry->phone) ?? '';

        return new Content(
            view: 'mail.new-inquiry',
            with: [
                'siteName' => Setting::read('site_name', 'en'),
                // Signed links: they only work for this inquiry and cannot be guessed or altered.
                'openUrl' => URL::temporarySignedRoute('inquiry.email.open', now()->addDays(60), ['inquiry' => $this->inquiry->id]),
                'pixelUrl' => URL::signedRoute('inquiry.email.seen', ['inquiry' => $this->inquiry->id]),
                'whatsappUrl' => 'https://wa.me/'.(str_starts_with($digits, '88') ? $digits : '88'.$digits),
                'telUrl' => 'tel:+'.(str_starts_with($digits, '88') ? $digits : '88'.$digits),
            ],
        );
    }
}
