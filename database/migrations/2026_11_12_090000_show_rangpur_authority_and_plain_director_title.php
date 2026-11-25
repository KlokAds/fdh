<?php

use App\Models\Faq;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Text the client asked to change, wherever it is already saved on a running site:
     * approvals name the Rangpur Development Authority, and the managing director is
     * introduced by that title alone (no degree or IEB after his name).
     *
     * @var array<string, string>
     */
    private array $replace = [
        // The managing director's title and introductions
        'Managing Director · BSc in Civil Engineering (IEB)' => 'Managing Director',
        'ব্যবস্থাপনা পরিচালক · বিএসসি ইন সিভিল ইঞ্জিনিয়ারিং (আইইবি)' => 'ব্যবস্থাপনা পরিচালক',
        'supervised by Engr. Noman, BSc in Civil Engineering (IEB).' => 'supervised by Engr. Noman.',
        'ইঞ্জি. নোমানের (বিএসসি সিভিল, আইইবি) তত্ত্বাবধানে' => 'ইঞ্জি. নোমানের তত্ত্বাবধানে',
        'an IEB-certified civil engineer' => 'a RAJUK-enlisted structural engineer',
        'আইইবি সনদপ্রাপ্ত সিভিল ইঞ্জিনিয়ার' => 'রাজউক তালিকাভুক্ত স্ট্রাকচারাল ইঞ্জিনিয়ার',
        // Approvals: the Rangpur Development Authority comes first
        'RAJUK / City Corp / Upazila approval sheet' => 'Rangpur Development Authority / City Corp / RAJUK / Upazila approval sheet',
        'রাজউক / সিটি কর্পোরেশন / উপজেলা অনুমোদন নকশা' => 'রংপুর উন্নয়ন কর্তৃপক্ষ / সিটি কর্পোরেশন / রাজউক / উপজেলা অনুমোদন নকশা',
        'RAJUK / City Corporation / Upazila approval' => 'Rangpur Development Authority / City Corporation / RAJUK approval',
        'রাজউক / সিটি কর্পোরেশন / উপজেলা অনুমোদন' => 'রংপুর উন্নয়ন কর্তৃপক্ষ / সিটি কর্পোরেশন / রাজউক অনুমোদন',
        'RAJUK, City Corporation and Upazila' => 'Rangpur Development Authority, RAJUK, City Corporation and Upazila',
        'রাজউক, সিটি কর্পোরেশন ও উপজেলা' => 'রংপুর উন্নয়ন কর্তৃপক্ষ, রাজউক, সিটি কর্পোরেশন ও উপজেলা',
        'RAJUK, City Corporation, Upazila' => 'Rangpur Development Authority, RAJUK, City Corporation, Upazila',
        'রাজউক, সিটি কর্পোরেশন, উপজেলা' => 'রংপুর উন্নয়ন কর্তৃপক্ষ, রাজউক, সিটি কর্পোরেশন, উপজেলা',
    ];

    public function up(): void
    {
        foreach (Setting::query()->get() as $setting) {
            $value = (string) $setting->value;
            $perLanguage = json_decode($value, true);

            // Translatable settings are stored as {"en": "…", "bn": "…"}; anything else is plain text.
            if (is_array($perLanguage)) {
                $reworded = array_map(fn (mixed $text): mixed => is_string($text) ? $this->rewordSetting($setting->key, $text) : $text, $perLanguage);

                if ($reworded !== $perLanguage) {
                    $setting->update(['value' => json_encode($reworded, JSON_UNESCAPED_UNICODE)]);
                }
            } elseif (($new = $this->rewordSetting($setting->key, $value)) !== $value) {
                $setting->update(['value' => $new]);
            }
        }

        foreach ([Service::class => ['summary', 'description', 'features'], Faq::class => ['answer']] as $model => $fields) {
            foreach ($model::query()->get() as $row) {
                foreach ($fields as $field) {
                    $row->setTranslations($field, array_map($this->reword(...), $row->getTranslations($field)));
                }

                $row->save();
            }
        }

        foreach (TeamMember::query()->get() as $member) {
            $bio = $member->getTranslations('bio');

            if ($member->is_lead && preg_match('/IEB|আইইবি/u', implode(' ', $bio)) === 1) {
                $member->setTranslations('bio', []);
                $member->save();
            }
        }

        Cache::forget('site-settings');
    }

    public function down(): void
    {
        // Wording only; the earlier text is not restored.
    }

    private function rewordSetting(string $key, string $text): string
    {
        return match ($key) {
            // The third figure on the home page read "IEB — certified civil engineer".
            'stat_3_value' => ['IEB' => '5', 'আইইবি' => '৫'][$text] ?? $text,
            'stat_3_label' => ['Certified civil engineer' => 'Services from one team', 'সনদপ্রাপ্ত সিভিল ইঞ্জিনিয়ার' => 'সেবা, এক দলে'][$text] ?? $text,
            default => $this->reword($text),
        };
    }

    private function reword(string $text): string
    {
        // Already names the authority: leave it, so the longer phrases are not extended twice.
        if (str_contains($text, 'Rangpur Development Authority') || str_contains($text, 'রংপুর উন্নয়ন কর্তৃপক্ষ')) {
            return strtr($text, array_slice($this->replace, 0, 6, true));
        }

        return strtr($text, $this->replace);
    }
};
