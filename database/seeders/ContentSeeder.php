<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Service;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Starting content in English and Bangla. Everything here is editable in the admin panel.
     */
    public function run(): void
    {
        $this->services();

        if (TeamMember::query()->doesntExist()) {
            TeamMember::query()->create([
                'name' => ['en' => 'Engr. Noman', 'bn' => 'ইঞ্জি. নোমান'],
                'designation' => ['en' => 'Managing Director', 'bn' => 'ব্যবস্থাপনা পরিচালক'],
                'bio' => ['en' => 'BSc in Civil Engineering (IEB).', 'bn' => 'বিএসসি ইন সিভিল ইঞ্জিনিয়ারিং (আইইবি)।'],
                'phone' => '01786-852453',
                'email' => 'engr.nomanbabu@gmail.com',
                'sort' => 1,
            ]);
        }

        if (Faq::query()->doesntExist()) {
            $this->faqs();
        }
    }

    private function services(): void
    {
        $services = [
            [
                'slug' => 'building-design',
                'icon' => 'building',
                'title' => ['en' => 'Building Design', 'bn' => 'বিল্ডিং ডিজাইন'],
                'summary' => ['en' => 'Complete, buildable drawing sets for homes, duplexes, apartments and commercial buildings.', 'bn' => 'বাড়ি, ডুপ্লেক্স, অ্যাপার্টমেন্ট ও বাণিজ্যিক ভবনের জন্য সম্পূর্ণ নির্মাণযোগ্য নকশা।'],
                'description' => [
                    'en' => "Every successful building starts with a clear, complete design. Our team prepares the full drawing set your project needs — architectural plans and elevations, structural design following BNBC, interior layouts, and electrical and plumbing drawings — all coordinated so contractors can build without guesswork.\n\nWe design around your land, your family's needs and your budget, and we revise until you are happy with every room.",
                    'bn' => "প্রতিটি সফল ভবনের শুরু একটি পরিষ্কার ও সম্পূর্ণ নকশা দিয়ে। আমাদের দল আপনার প্রকল্পের প্রয়োজনীয় সব নকশা তৈরি করে — আর্কিটেকচারাল প্ল্যান ও এলিভেশন, বিএনবিসি অনুযায়ী স্ট্রাকচারাল ডিজাইন, ইন্টেরিয়র লেআউট এবং ইলেকট্রিক্যাল ও প্লাম্বিং নকশা — যাতে ঠিকাদার কোনো অনুমান ছাড়াই নির্মাণ করতে পারেন।\n\nআপনার জমি, পরিবারের প্রয়োজন ও বাজেট অনুযায়ী আমরা ডিজাইন করি এবং প্রতিটি ঘর আপনার পছন্দ না হওয়া পর্যন্ত সংশোধন করি।",
                ],
                'features' => ['en' => 'Architectural, Structural, Interior, Electrical, Plumbing', 'bn' => 'আর্কিটেকচারাল, স্ট্রাকচারাল, ইন্টেরিয়র, ইলেকট্রিক্যাল, প্লাম্বিং'],
            ],
            [
                'slug' => 'approval-sheet',
                'icon' => 'approval',
                'title' => ['en' => 'Approval Sheet', 'bn' => 'অনুমোদন নকশা'],
                'summary' => ['en' => 'Drawings prepared to authority standards and full support until your plan is approved.', 'bn' => 'কর্তৃপক্ষের মান অনুযায়ী নকশা প্রস্তুত এবং অনুমোদন না হওয়া পর্যন্ত পূর্ণ সহায়তা।'],
                'description' => [
                    'en' => "Getting a building plan approved can be slow and confusing. We prepare approval drawings to the standards of RAJUK, City Corporation and Upazila authorities, and guide you through the documents and steps involved.\n\nCorrect drawings the first time mean fewer objections and a faster approval.",
                    'bn' => "ভবনের নকশা অনুমোদন করানো সময়সাপেক্ষ ও জটিল হতে পারে। আমরা রাজউক, সিটি কর্পোরেশন ও উপজেলা কর্তৃপক্ষের মান অনুযায়ী অনুমোদন নকশা প্রস্তুত করি এবং প্রয়োজনীয় কাগজপত্র ও ধাপগুলোতে আপনাকে সহায়তা করি।\n\nপ্রথমবারেই সঠিক নকশা মানে কম আপত্তি এবং দ্রুত অনুমোদন।",
                ],
                'features' => ['en' => 'RAJUK, City Corporation, Upazila', 'bn' => 'রাজউক, সিটি কর্পোরেশন, উপজেলা'],
            ],
            [
                'slug' => 'construction-management',
                'icon' => 'construction',
                'title' => ['en' => 'Construction Management & Supervision', 'bn' => 'নির্মাণ ব্যবস্থাপনা ও তত্ত্বাবধান'],
                'summary' => ['en' => 'Engineers on site checking reinforcement, concrete, quality and schedule.', 'bn' => 'রড, ঢালাই, মান ও সময়সূচি যাচাইয়ে সাইটে ইঞ্জিনিয়ারদের তত্ত্বাবধান।'],
                'description' => [
                    'en' => "A good design only matters if it is built correctly. Our engineers supervise the critical stages on site — layout, foundation, reinforcement, casting and finishing — so what is built matches what was designed.\n\nYou get regular progress updates and peace of mind that your money is going into a safe, quality structure.",
                    'bn' => "ভালো নকশার মূল্য তখনই, যখন তা সঠিকভাবে নির্মিত হয়। আমাদের ইঞ্জিনিয়াররা সাইটের গুরুত্বপূর্ণ ধাপগুলো — লেআউট, ফাউন্ডেশন, রড বাঁধাই, ঢালাই ও ফিনিশিং — তত্ত্বাবধান করেন, যাতে নকশা অনুযায়ীই নির্মাণ হয়।\n\nআপনি নিয়মিত অগ্রগতির তথ্য পাবেন এবং নিশ্চিন্ত থাকবেন যে আপনার টাকা একটি নিরাপদ ও মানসম্মত ভবনে ব্যয় হচ্ছে।",
                ],
                'features' => ['en' => 'Site supervision, Quality control, Progress reporting', 'bn' => 'সাইট তত্ত্বাবধান, মান নিয়ন্ত্রণ, অগ্রগতি প্রতিবেদন'],
            ],
            [
                'slug' => 'digital-survey-soil-test',
                'icon' => 'survey',
                'title' => ['en' => 'Digital Survey & Soil Test', 'bn' => 'ডিজিটাল সার্ভে ও সয়েল টেস্ট'],
                'summary' => ['en' => 'Accurate land measurement and soil investigation — the foundation of a safe structural design.', 'bn' => 'নির্ভুল ভূমি পরিমাপ ও মাটি পরীক্ষা — নিরাপদ স্ট্রাকচারাল ডিজাইনের ভিত্তি।'],
                'description' => [
                    'en' => "Before designing, we need to know the land. Our digital survey gives accurate plot measurements, boundaries and levels, and our soil test reveals the bearing capacity of the ground.\n\nThis data lets our engineers choose the right foundation — safe, and not over-designed or over-priced.",
                    'bn' => "ডিজাইনের আগে জমিকে জানা প্রয়োজন। আমাদের ডিজিটাল সার্ভে জমির নির্ভুল মাপ, সীমানা ও লেভেল দেয়, আর সয়েল টেস্ট মাটির ভারবহন ক্ষমতা নির্ণয় করে।\n\nএই তথ্যের ভিত্তিতে ইঞ্জিনিয়াররা সঠিক ফাউন্ডেশন নির্বাচন করেন — নিরাপদ, কিন্তু অপ্রয়োজনীয় খরচ ছাড়া।",
                ],
                'features' => ['en' => 'Digital land survey, Soil test, Foundation advice', 'bn' => 'ডিজিটাল ভূমি জরিপ, মাটি পরীক্ষা, ফাউন্ডেশন পরামর্শ'],
            ],
            [
                'slug' => 'cost-estimation',
                'icon' => 'estimate',
                'title' => ['en' => 'Cost Estimation', 'bn' => 'খরচ নির্ধারণ'],
                'summary' => ['en' => 'Detailed BOQ and material estimates so you know the real budget before you build.', 'bn' => 'বিস্তারিত বিওকিউ ও উপকরণের হিসাব — নির্মাণের আগেই প্রকৃত বাজেট জানুন।'],
                'description' => [
                    'en' => "Know your budget before you start. We prepare detailed cost estimates and bills of quantities (BOQ) based on your actual drawings — materials, labour and stage-wise costs.\n\nWith a realistic estimate you can plan financing, compare contractor quotes and avoid surprises halfway through construction.",
                    'bn' => "শুরুর আগেই বাজেট জানুন। আপনার প্রকৃত নকশার ভিত্তিতে আমরা বিস্তারিত খরচের হিসাব ও বিল অব কোয়ান্টিটি (বিওকিউ) প্রস্তুত করি — উপকরণ, শ্রম ও ধাপভিত্তিক খরচসহ।\n\nবাস্তবসম্মত হিসাব থাকলে অর্থায়ন পরিকল্পনা, ঠিকাদারের দর তুলনা এবং নির্মাণের মাঝপথে অপ্রত্যাশিত খরচ এড়ানো সহজ হয়।",
                ],
                'features' => ['en' => 'BOQ, Material estimate, Stage-wise budget', 'bn' => 'বিওকিউ, উপকরণের হিসাব, ধাপভিত্তিক বাজেট'],
            ],
        ];

        foreach ($services as $index => $service) {
            Service::query()->updateOrCreate(['slug' => $service['slug']], $service + ['sort' => $index + 1, 'is_active' => true]);
        }
    }

    private function faqs(): void
    {
        $faqs = [
            [
                ['en' => 'What services does Friends Dream Home provide?', 'bn' => 'ফ্রেন্ডস ড্রিম হোম কী কী সেবা দেয়?'],
                ['en' => 'We provide complete building design (architectural, structural, interior, electrical and plumbing), approval drawings for RAJUK, City Corporation and Upazila, construction management and supervision, digital survey and soil test, and cost estimation.', 'bn' => 'আমরা সম্পূর্ণ বিল্ডিং ডিজাইন (আর্কিটেকচারাল, স্ট্রাকচারাল, ইন্টেরিয়র, ইলেকট্রিক্যাল ও প্লাম্বিং), রাজউক, সিটি কর্পোরেশন ও উপজেলার অনুমোদন নকশা, নির্মাণ ব্যবস্থাপনা ও তত্ত্বাবধান, ডিজিটাল সার্ভে ও সয়েল টেস্ট এবং খরচ নির্ধারণ সেবা দিই।'],
            ],
            [
                ['en' => 'Can you help me get my building plan approved?', 'bn' => 'আমার ভবনের নকশা অনুমোদনে কি আপনারা সাহায্য করতে পারবেন?'],
                ['en' => 'Yes. We prepare approval drawings to the standards of RAJUK, City Corporation and Upazila authorities and guide you through the required documents and steps.', 'bn' => 'হ্যাঁ। আমরা রাজউক, সিটি কর্পোরেশন ও উপজেলা কর্তৃপক্ষের মান অনুযায়ী অনুমোদন নকশা প্রস্তুত করি এবং প্রয়োজনীয় কাগজপত্র ও ধাপগুলোতে আপনাকে সহায়তা করি।'],
            ],
            [
                ['en' => 'Why do I need a soil test before designing?', 'bn' => 'ডিজাইনের আগে সয়েল টেস্ট কেন প্রয়োজন?'],
                ['en' => 'The soil test tells our engineers how much load the ground can carry. With that information the foundation can be designed safely without spending more than necessary.', 'bn' => 'সয়েল টেস্ট থেকে ইঞ্জিনিয়াররা জানতে পারেন মাটি কতটা ভার বহন করতে পারে। এই তথ্যের ভিত্তিতে অপ্রয়োজনীয় খরচ ছাড়াই নিরাপদ ফাউন্ডেশন ডিজাইন করা যায়।'],
            ],
            [
                ['en' => 'How do I get a cost estimate for my building?', 'bn' => 'আমার ভবনের খরচের হিসাব কীভাবে পাব?'],
                ['en' => 'Send us your land size, number of floors and requirements through the contact form, phone or WhatsApp. We prepare a detailed estimate (BOQ) based on the drawings.', 'bn' => 'যোগাযোগ ফর্ম, ফোন বা হোয়াটসঅ্যাপে আপনার জমির পরিমাণ, তলার সংখ্যা ও প্রয়োজনীয়তা জানান। নকশার ভিত্তিতে আমরা বিস্তারিত খরচের হিসাব (বিওকিউ) প্রস্তুত করি।'],
            ],
            [
                ['en' => 'Where are your offices?', 'bn' => 'আপনাদের অফিস কোথায়?'],
                ['en' => 'Our head office is on Thana Road, Rangpur, and we also have an office in South Banasree, Dhaka. Full addresses are on the Contact page.', 'bn' => 'আমাদের প্রধান অফিস রংপুরের থানা রোডে, এছাড়া ঢাকার দক্ষিণ বনশ্রীতেও অফিস রয়েছে। পূর্ণ ঠিকানা যোগাযোগ পাতায় দেওয়া আছে।'],
            ],
        ];

        foreach ($faqs as $index => [$question, $answer]) {
            Faq::query()->create(['question' => $question, 'answer' => $answer, 'sort' => $index + 1]);
        }
    }
}
