<?php

use App\Models\Faq;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * The client's list of approving authorities: Rangpur Development Authority, RAJUK,
     * City Corporation and Pourashava. Sites that ran the previous update hold the list
     * without Pourashava; this completes it.
     *
     * @var array<string, string>
     */
    private array $replace = [
        'Rangpur Development Authority / City Corp / RAJUK / Upazila approval sheet' => 'Rangpur Development Authority / RAJUK / City Corp / Pourashava approval sheet',
        'রংপুর উন্নয়ন কর্তৃপক্ষ / সিটি কর্পোরেশন / রাজউক / উপজেলা অনুমোদন নকশা' => 'রংপুর উন্নয়ন কর্তৃপক্ষ / রাজউক / সিটি কর্পোরেশন / পৌরসভা অনুমোদন নকশা',
        'Rangpur Development Authority / City Corporation / RAJUK approval' => 'Rangpur Development Authority / RAJUK / City Corporation / Pourashava approval',
        'রংপুর উন্নয়ন কর্তৃপক্ষ / সিটি কর্পোরেশন / রাজউক অনুমোদন' => 'রংপুর উন্নয়ন কর্তৃপক্ষ / রাজউক / সিটি কর্পোরেশন / পৌরসভা অনুমোদন',
        'Rangpur Development Authority, RAJUK, City Corporation and Upazila' => 'Rangpur Development Authority, RAJUK, City Corporation, Pourashava and Upazila',
        'রংপুর উন্নয়ন কর্তৃপক্ষ, রাজউক, সিটি কর্পোরেশন ও উপজেলা' => 'রংপুর উন্নয়ন কর্তৃপক্ষ, রাজউক, সিটি কর্পোরেশন, পৌরসভা ও উপজেলা',
        'Rangpur Development Authority, RAJUK, City Corporation, Upazila' => 'Rangpur Development Authority, RAJUK, City Corporation, Pourashava, Upazila',
        'রংপুর উন্নয়ন কর্তৃপক্ষ, রাজউক, সিটি কর্পোরেশন, উপজেলা' => 'রংপুর উন্নয়ন কর্তৃপক্ষ, রাজউক, সিটি কর্পোরেশন, পৌরসভা, উপজেলা',
    ];

    public function up(): void
    {
        foreach (Setting::query()->get() as $setting) {
            $perLanguage = json_decode((string) $setting->value, true);

            // Only translatable settings ({"en": "…", "bn": "…"}) carry this wording.
            if (! is_array($perLanguage)) {
                continue;
            }

            $reworded = array_map(fn (mixed $text): mixed => is_string($text) ? strtr($text, $this->replace) : $text, $perLanguage);

            if ($reworded !== $perLanguage) {
                $setting->update(['value' => json_encode($reworded, JSON_UNESCAPED_UNICODE)]);
            }
        }

        foreach ([Service::class => ['summary', 'description', 'features'], Faq::class => ['answer']] as $model => $fields) {
            foreach ($model::query()->get() as $row) {
                foreach ($fields as $field) {
                    $row->setTranslations($field, array_map(fn (string $text): string => strtr($text, $this->replace), $row->getTranslations($field)));
                }

                $row->save();
            }
        }

        Cache::forget('site-settings');
    }

    public function down(): void
    {
        // Wording only; the earlier text is not restored.
    }
};
