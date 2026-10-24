<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('fdh.locales')), 404);

        $request->session()->put('locale', $locale);

        // Only ever redirect back to a page on this site.
        $previous = url()->previous();
        $target = str_starts_with($previous, url('/')) ? $previous : route('home');

        return redirect()->to($target);
    }
}
