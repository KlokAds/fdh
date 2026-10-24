<?php

use App\Http\Controllers\EditorImageController;
use App\Http\Controllers\InquiryEmailController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SeoController;
use App\Livewire\Admin;
use App\Livewire\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Site\Home::class)->name('home');
Route::livewire('/ab', Site\About::class)->name('about');
Route::livewire('/sv', Site\Services::class)->name('services');
Route::livewire('/sv/{service}', Site\ServiceShow::class)->name('services.show');
Route::livewire('/pt', Site\Projects::class)->name('projects');
Route::livewire('/pt/{project}', Site\ProjectShow::class)->name('projects.show');
Route::livewire('/cc', Site\Calculator::class)->name('calculator');
Route::livewire('/tm', Site\Team::class)->name('team');
Route::livewire('/qa', Site\Faq::class)->name('faq');
Route::livewire('/ct', Site\Contact::class)->name('contact');

// The earlier, longer addresses keep working: shared links and search results move to the short ones.
foreach (['about' => 'ab', 'services' => 'sv', 'projects' => 'pt', 'cost-calculator' => 'cc', 'team' => 'tm', 'faq' => 'qa', 'contact' => 'ct'] as $old => $new) {
    Route::get("/{$old}", fn (Request $request) => redirect("/{$new}".($request->getQueryString() ? '?'.$request->getQueryString() : ''), 301));
}
Route::get('/services/{slug}', fn (string $slug) => redirect("/sv/{$slug}", 301))->where('slug', '[A-Za-z0-9\-]+');
Route::get('/projects/{slug}', fn (string $slug) => redirect("/pt/{$slug}", 301))->where('slug', '[A-Za-z0-9\-]+');

Route::get('/lang/{locale}', LocaleController::class)->middleware('throttle:30,1')->name('locale');

Route::middleware(['signed', 'throttle:60,1'])->prefix('mail/inquiry/{inquiry}')->name('inquiry.email.')->group(function (): void {
    Route::get('/seen.gif', [InquiryEmailController::class, 'seen'])->name('seen');
    Route::get('/open', [InquiryEmailController::class, 'open'])->name('open');
});
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::prefix(config('fdh.admin_path'))->name('admin.')->group(function (): void {
    // Served from the admin path so the service worker's scope covers the whole panel.
    Route::get('/sw.js', [PushSubscriptionController::class, 'serviceWorker'])->name('push.worker');
    Route::get('/manifest.webmanifest', [PushSubscriptionController::class, 'manifest'])->name('manifest');

    Route::middleware('guest')->group(function (): void {
        Route::livewire('/login', Admin\Login::class)->name('login');
        Route::livewire('/two-factor-challenge', Admin\TwoFactorChallenge::class)->name('two-factor');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('/logout', LogoutController::class)->name('logout');
        Route::livewire('/security', Admin\Security::class)->name('security');

        Route::middleware('two-factor')->group(function (): void {
            Route::livewire('/', Admin\Dashboard::class)->name('dashboard');
            Route::livewire('/inquiries', Admin\Inquiries::class)->middleware('can:inquiries.view')->name('inquiries');
            Route::livewire('/notifications', Admin\Notifications::class)->middleware('can:inquiries.view')->name('notifications');
            Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->middleware(['can:inquiries.view', 'throttle:20,1'])->name('push.subscribe');
            Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->middleware('throttle:20,1')->name('push.unsubscribe');
            Route::livewire('/content', Admin\Content::class)->name('content');
            Route::livewire('/services', Admin\Services::class)->middleware('can:services.manage')->name('services');
            Route::livewire('/projects', Admin\Projects::class)->middleware('can:projects.manage')->name('projects');
            Route::livewire('/team', Admin\Team::class)->middleware('can:team.manage')->name('team');
            Route::livewire('/faqs', Admin\Faqs::class)->middleware('can:faqs.manage')->name('faqs');
            Route::livewire('/testimonials', Admin\Testimonials::class)->middleware('can:testimonials.manage')->name('testimonials');
            Route::post('/editor-image', [EditorImageController::class, 'store'])->middleware('throttle:30,1')->name('editor-image');
            Route::livewire('/media', Admin\Media::class)->middleware('can:media.manage')->name('media');
            Route::livewire('/users', Admin\Users::class)->middleware('can:users.manage')->name('users');
            Route::livewire('/roles', Admin\Roles::class)->middleware('can:users.manage')->name('roles');
            Route::livewire('/activity', Admin\Activity::class)->middleware('can:logs.view')->name('activity');
            Route::livewire('/system-update', Admin\SystemUpdate::class)->middleware('can:system.update')->name('system-update');
        });
    });
});
