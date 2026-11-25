<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientWordingUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_text_saved_before_the_update_is_reworded_once(): void
    {
        $this->seed(ContentSeeder::class);

        // What a site installed before this update still holds.
        Setting::write([
            'md_title' => ['en' => 'Managing Director · BSc in Civil Engineering (IEB)', 'bn' => 'ব্যবস্থাপনা পরিচালক · বিএসসি ইন সিভিল ইঞ্জিনিয়ারিং (আইইবি)'],
            'process_4_text' => ['en' => 'Detailed cost estimate and RAJUK / City Corp / Upazila approval sheet.', 'bn' => 'বিস্তারিত খরচের হিসাব এবং রাজউক / সিটি কর্পোরেশন / উপজেলা অনুমোদন নকশা।'],
            'stat_3_value' => ['en' => 'IEB', 'bn' => 'আইইবি'],
            'hero_cta' => ['en' => 'Free advice in Rangpur', 'bn' => 'রংপুরে ফ্রি পরামর্শ নিন'],
        ]);
        Service::query()->where('slug', 'approval-sheet')->firstOrFail()
            ->setTranslations('features', ['en' => 'RAJUK, City Corporation, Upazila', 'bn' => 'রাজউক, সিটি কর্পোরেশন, উপজেলা'])->save();
        $director = TeamMember::query()->firstOrFail();
        $director->forceFill(['is_lead' => true])->setTranslations('bio', ['en' => 'BSc in Civil Engineering (IEB)', 'bn' => 'বিএসসি ইন সিভিল ইঞ্জিনিয়ারিং (আইইবি)'])->save();
        $engineer = TeamMember::factory()->create(['bio' => ['en' => 'BSc in Civil Engineering, BUET · MIEB', 'bn' => 'বিএসসি, বুয়েট · এমআইইবি']]);

        $update = require database_path('migrations/2026_11_12_090000_show_rangpur_authority_and_plain_director_title.php');
        $update->up();
        $update->up();
        $authorities = require database_path('migrations/2026_11_12_100000_add_pourashava_to_approval_authorities.php');
        $authorities->up();
        $authorities->up();

        $this->assertSame('ব্যবস্থাপনা পরিচালক', Setting::read('md_title', 'bn'));
        $this->assertSame('Managing Director', Setting::read('md_title', 'en'));
        $this->assertSame('বিস্তারিত খরচের হিসাব এবং রংপুর উন্নয়ন কর্তৃপক্ষ / রাজউক / সিটি কর্পোরেশন / পৌরসভা অনুমোদন নকশা।', Setting::read('process_4_text', 'bn'));
        $this->assertSame('৫', Setting::read('stat_3_value', 'bn'));
        $this->assertSame('রংপুরে ফ্রি পরামর্শ নিন', Setting::read('hero_cta', 'bn'));
        $this->assertSame('রংপুর উন্নয়ন কর্তৃপক্ষ, রাজউক, সিটি কর্পোরেশন, পৌরসভা, উপজেলা', Service::query()->where('slug', 'approval-sheet')->firstOrFail()->getTranslation('features', 'bn'));
        $this->assertSame([], $director->fresh()->getTranslations('bio'));
        $this->assertSame('BSc in Civil Engineering, BUET · MIEB', $engineer->fresh()->getTranslation('bio', 'en'));
    }

    public function test_the_site_names_the_rangpur_authority_and_only_the_directors_title(): void
    {
        $this->seed(ContentSeeder::class);
        app()->setLocale('bn');

        $this->get('/')->assertOk()
            ->assertSee('রংপুর উন্নয়ন কর্তৃপক্ষের অনুমোদন')
            ->assertDontSee('আইইবি');
        $this->get(route('about'))->assertOk()->assertDontSee('আইইবি');
    }

    public function test_the_rangpur_office_address_follows_the_move_to_nilkontho_road(): void
    {
        $this->seed(ContentSeeder::class);

        // A site installed while the office was on Thana Road.
        Setting::write([
            'rangpur_address' => ['en' => 'House-229, Road-3, Thana Road, Rangpur', 'bn' => 'বাড়ি-২২৯, রোড-৩, থানা রোড, রংপুর'],
            'dhaka_address' => ['en' => 'House-31, Road-10, Block-L, South Banasree, Dhaka', 'bn' => 'বাড়ি-৩১, রোড-১০, ব্লক-এল, দক্ষিণ বনশ্রী, ঢাকা'],
        ]);
        $offices = Faq::query()->get()->firstOrFail(fn (Faq $faq): bool => str_contains($faq->getTranslation('question', 'en'), 'offices'));
        $offices->setTranslation('answer', 'bn', 'আমাদের প্রধান অফিস রংপুরের থানা রোডে, এছাড়া ঢাকার দক্ষিণ বনশ্রীতেও অফিস রয়েছে।')->save();

        $move = require database_path('migrations/2026_11_25_061000_move_rangpur_office_to_nilkontho_road.php');
        $move->up();
        $move->up();

        $this->assertSame('নীলকণ্ঠ রোড, পাকার মাথা, সদর, রংপুর ৫৪০১', Setting::read('rangpur_address', 'bn'));
        $this->assertSame('Nilkontho Road, Pakar Matha, Sadar, Rangpur 5401', Setting::read('rangpur_address', 'en'));
        $this->assertSame('বাড়ি-৩১, রোড-১০, ব্লক-এল, দক্ষিণ বনশ্রী, ঢাকা', Setting::read('dhaka_address', 'bn'));
        $this->assertSame('আমাদের প্রধান অফিস রংপুরের পাকার মাথায়, নীলকণ্ঠ রোডে, এছাড়া ঢাকার দক্ষিণ বনশ্রীতেও অফিস রয়েছে।', $offices->fresh()->getTranslation('answer', 'bn'));

        app()->setLocale('bn');
        $this->get(route('contact'))->assertOk()->assertSee('নীলকণ্ঠ রোড, পাকার মাথা, সদর, রংপুর ৫৪০১')->assertDontSee('থানা রোড');
    }
}
