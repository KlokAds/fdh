<?php

use App\Models\Faq;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * The Rangpur office is on Nilkontho Road, Pakar Matha. Sites that saved the earlier
     * Thana Road address, or the FAQ answer that names it, get the new one.
     *
     * @var array<string, string>
     */
    private array $address = [
        'en' => 'Nilkontho Road, Pakar Matha, Sadar, Rangpur 5401',
        'bn' => 'নীলকণ্ঠ রোড, পাকার মাথা, সদর, রংপুর ৫৪০১',
    ];

    /** @var array<string, string> */
    private array $replace = [
        'Our head office is on Thana Road, Rangpur' => 'Our head office is on Nilkontho Road, Pakar Matha, Rangpur',
        'আমাদের প্রধান অফিস রংপুরের থানা রোডে' => 'আমাদের প্রধান অফিস রংপুরের পাকার মাথায়, নীলকণ্ঠ রোডে',
    ];

    public function up(): void
    {
        $saved = Setting::query()->find('rangpur_address');
        $perLanguage = $saved ? json_decode((string) $saved->value, true) : null;

        if (is_array($perLanguage)) {
            $moved = $perLanguage;

            foreach ($this->address as $language => $address) {
                if (preg_match('/Thana Road|থানা রোড/u', (string) ($perLanguage[$language] ?? '')) === 1) {
                    $moved[$language] = $address;
                }
            }

            if ($moved !== $perLanguage) {
                $saved->update(['value' => json_encode($moved, JSON_UNESCAPED_UNICODE)]);
            }
        }

        foreach (Faq::query()->get() as $faq) {
            $faq->setTranslations('answer', array_map(fn (string $text): string => strtr($text, $this->replace), $faq->getTranslations('answer')));
            $faq->save();
        }

        Cache::forget('site-settings');
    }

    public function down(): void
    {
        // The office has moved; the earlier address is not restored.
    }
};
