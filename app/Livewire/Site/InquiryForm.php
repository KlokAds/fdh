<?php

namespace App\Livewire\Site;

use App\Mail\InquiryReceivedMail;
use App\Mail\NewInquiryMail;
use App\Models\Inquiry;
use App\Models\Service;
use App\Models\Setting;
use App\Rules\EmailList;
use App\Support\MetaConversions;
use App\Support\PushNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class InquiryForm extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $service = '';

    public string $location = '';

    public string $message = '';

    /** Honeypot: hidden from people, filled in by bots. */
    public string $website = '';

    #[Locked]
    public int $startedAt = 0;

    public bool $sent = false;

    public function mount(?string $service = null, ?string $message = null): void
    {
        $this->message = mb_substr(strip_tags(trim((string) $message)), 0, 500);
        $this->startedAt = now()->getTimestamp();
        $this->service = $service !== null && in_array($service, $this->serviceOptions(), true) ? $service : '';
    }

    /**
     * Service names in the visitor's language, plus general options.
     *
     * @return list<string>
     */
    public function serviceOptions(): array
    {
        return array_keys($this->serviceChoices());
    }

    /**
     * Service names in the visitor's language mapped to the icon shown in the dropdown.
     *
     * @return array<string, string>
     */
    private function serviceChoices(): array
    {
        return Service::query()->active()->get()
            ->mapWithKeys(fn (Service $service): array => [(string) $service->title => $service->icon])
            ->put(__('Full package (design to construction)'), 'layers')
            ->put(__('Other'), 'chat')
            ->all();
    }

    /**
     * Pick a service by its position in the list, so the browser never sends free text.
     */
    public function chooseService(int $index): void
    {
        $this->service = $this->serviceOptions()[$index] ?? '';
        $this->resetErrorBag('service');
    }

    public function submit(): void
    {
        // Silently accept bot submissions (honeypot filled or form sent impossibly fast) without saving them.
        if ($this->website !== '' || now()->getTimestamp() - $this->startedAt < 3) {
            $this->sent = true;

            return;
        }

        $key = 'inquiry:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, config('fdh.inquiry_per_hour'))) {
            $this->addError('form', __('You have sent several requests already. Please call us at :phone.', ['phone' => site('phone')]));

            return;
        }

        // Bangla keyboards type ০-৯; the number is stored with Latin digits.
        $this->phone = strtr($this->phone, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        $this->phone = preg_replace('/[\s\-()]/u', '', $this->phone) ?? '';

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'phone' => ['required', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'email' => 'nullable|email|max:150',
            'service' => ['nullable', Rule::in($this->serviceOptions())],
            'location' => 'nullable|string|max:150',
            'message' => 'nullable|string|max:2000',
        ], [
            'name.required' => __('Please enter your name.'),
            'name.min' => __('Please enter your name.'),
            'name.regex' => __('Please enter your name using letters only.'),
            'phone.required' => __('Please enter your mobile number.'),
            'phone.regex' => __('Please enter a valid Bangladeshi mobile number (e.g. 01712345678).'),
            'email.email' => __('Please enter a valid email address.'),
            'service.in' => __('Please choose a service from the list.'),
            '*.max' => __('This field is too long.'),
        ]);

        RateLimiter::hit($key, 3600);

        $inquiry = Inquiry::query()->create(array_map(fn ($value) => is_string($value) ? strip_tags(trim($value)) : $value, $data) + [
            'locale' => app()->getLocale(),
            'ip' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 250),
        ]);

        $this->notifyOffice($inquiry);
        $this->trackLead($inquiry);

        $this->sent = true;
    }

    /**
     * Report the inquiry to Meta as a Lead: from the browser pixel and from the server, with one shared event ID.
     */
    private function trackLead(Inquiry $inquiry): void
    {
        if (MetaConversions::pixelId() === '') {
            return;
        }

        $eventId = 'lead-'.$inquiry->id.'-'.Str::lower(Str::random(8));
        $this->dispatch('fdh-lead', eventId: $eventId, service: (string) $inquiry->service);

        $previous = url()->previous();
        $context = [
            'ip' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 400, ''),
            'fbp' => Str::limit((string) request()->cookie('_fbp'), 255, '') ?: null,
            'fbc' => Str::limit((string) request()->cookie('_fbc'), 255, '') ?: null,
            'url' => is_site_url($previous) ? $previous : route('contact'),
        ];

        defer(fn () => app(MetaConversions::class)->lead($inquiry, $eventId, $context));
    }

    public function whatsappLink(): string
    {
        $text = __("Hello :site, I'm :name. I need help with: :service.", [
            'site' => site('site_name'),
            'name' => $this->name,
            'service' => $this->service ?: __('a building project'),
        ]);

        return 'https://wa.me/'.site('whatsapp').'?text='.rawurlencode($text);
    }

    private function notifyOffice(Inquiry $inquiry): void
    {
        $recipients = EmailList::parse(Setting::read('notify_email', 'en'));

        // Send after the response so the visitor never waits on the mail server or push service.
        defer(function () use ($inquiry, $recipients): void {
            app(PushNotifier::class)->newInquiry($inquiry);

            if ($recipients !== []) {
                try {
                    Mail::to($recipients)->send(new NewInquiryMail($inquiry));
                    $inquiry->forceFill(['emailed_at' => now()])->save();
                } catch (Throwable $e) {
                    Log::warning("Inquiry #{$inquiry->id} notification failed: ".$e->getMessage());
                }
            }

            // A short, branded confirmation to the visitor when they left an email address.
            if ($inquiry->email) {
                try {
                    Mail::to($inquiry->email)->send(new InquiryReceivedMail($inquiry));
                } catch (Throwable $e) {
                    Log::warning("Inquiry #{$inquiry->id} auto-reply failed: ".$e->getMessage());
                }
            }
        });
    }

    public function render(): View
    {
        return view('livewire.site.inquiry-form', ['choices' => $this->serviceChoices()]);
    }
}
