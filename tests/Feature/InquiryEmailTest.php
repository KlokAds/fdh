<?php

namespace Tests\Feature;

use App\Livewire\Site\InquiryForm;
use App\Mail\InquiryReceivedMail;
use App\Mail\NewInquiryMail;
use App\Models\Inquiry;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class InquiryEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_inquiry_is_emailed_to_every_notification_address(): void
    {
        Mail::fake();
        $this->withoutDefer();
        Setting::write(['notify_email' => 'office@example.com, engineer@example.com']);

        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();
        $component->set('name', 'Karim')->set('phone', '01812345678')->call('submit')->assertSet('sent', true);

        Mail::assertSent(NewInquiryMail::class, fn (NewInquiryMail $mail): bool => $mail->hasTo('office@example.com') && $mail->hasTo('engineer@example.com'));
        $this->assertNotNull(Inquiry::query()->first()->emailed_at);
    }

    public function test_visitor_with_an_email_gets_a_branded_reply_in_their_language(): void
    {
        Mail::fake();
        $this->withoutDefer();
        Setting::write(['notify_email' => 'office@example.com']);

        app()->setLocale('bn');
        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();
        $component->set('name', 'Karim')->set('phone', '01812345678')->set('email', 'karim@example.com')->call('submit');

        Mail::assertSent(InquiryReceivedMail::class, function (InquiryReceivedMail $mail): bool {
            $html = $mail->render();

            return $mail->hasTo('karim@example.com')
                && str_contains($html, 'FDH-00001')
                && str_contains($html, 'আপনার অনুরোধটি আমরা পেয়েছি');
        });
        Mail::assertSent(NewInquiryMail::class, fn (NewInquiryMail $mail): bool => $mail->hasReplyTo('karim@example.com'));
    }

    public function test_no_email_when_no_address_is_set(): void
    {
        Mail::fake();
        $this->withoutDefer();
        Setting::write(['notify_email' => '']);

        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();
        $component->set('name', 'Karim')->set('phone', '01812345678')->call('submit');

        Mail::assertNothingSent();
    }

    public function test_opening_the_email_marks_the_inquiry_read(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->get(URL::signedRoute('inquiry.email.seen', ['inquiry' => $inquiry->id]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/gif');

        $inquiry->refresh();
        $this->assertNotNull($inquiry->read_at);
        $this->assertSame('email_open', $inquiry->read_via);
    }

    public function test_email_button_marks_read_and_opens_the_admin(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->get(URL::temporarySignedRoute('inquiry.email.open', now()->addDay(), ['inquiry' => $inquiry->id]))
            ->assertRedirect(route('admin.inquiries', ['open' => $inquiry->id]));

        $this->assertSame('email_link', $inquiry->fresh()->read_via);
    }

    public function test_tracking_links_cannot_be_forged(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->get(route('inquiry.email.seen', ['inquiry' => $inquiry->id]))->assertForbidden();
        $this->get(route('inquiry.email.open', ['inquiry' => $inquiry->id]))->assertForbidden();

        $other = Inquiry::factory()->create();
        $signedForFirst = URL::signedRoute('inquiry.email.seen', ['inquiry' => $inquiry->id]);
        $this->get(str_replace("/inquiry/{$inquiry->id}/", "/inquiry/{$other->id}/", $signedForFirst))->assertForbidden();

        $this->assertNull($inquiry->fresh()->read_at);
        $this->assertNull($other->fresh()->read_at);
    }

    public function test_email_renders_with_signed_links(): void
    {
        $inquiry = Inquiry::factory()->create(['name' => 'Karim', 'message' => 'Three floors in Rangpur']);

        $html = (new NewInquiryMail($inquiry))->render();

        $this->assertStringContainsString('Karim', $html);
        $this->assertStringContainsString('Three floors in Rangpur', $html);
        $this->assertStringContainsString('signature=', $html);
    }
}
