<?php

namespace App\Support;

use App\Rules\EmailList;

/**
 * Registry of every editable setting and page text on the public site.
 *
 * Each field: type (text|textarea|email|url|color|select|image), translatable (stored as {"en","bn"}),
 * label, default, and optional options/rules. Groups map to tabs in the admin and to the permission needed to edit them.
 */
class SiteContent
{
    /**
     * @return array<string, array{label: string, permission: string, fields: array<string, array<string, mixed>>}>
     */
    public static function groups(): array
    {
        return [
            'branding' => [
                'label' => 'Branding & theme',
                'permission' => 'settings.manage',
                'fields' => [
                    'site_name' => self::tr('Site name', 'Friends Dream Home', 'ফ্রেন্ডস ড্রিম হোম'),
                    'site_tagline' => self::tr('Tagline (under the logo)', 'Design & Consultancy', 'ডিজাইন ও কনসালটেন্সি'),
                    'logo' => ['type' => 'image', 'label' => 'Logo', 'size' => '512 × 512 px · square, transparent PNG', 'default' => ''],
                    'accent_color' => ['type' => 'color', 'label' => 'Accent colour (buttons, links)', 'default' => '#1596dc'],
                    'brand_dark' => ['type' => 'color', 'label' => 'Brand dark colour (header, hero, footer)', 'default' => '#0a1628'],
                    'default_locale' => ['type' => 'select', 'label' => 'Default language for new visitors', 'default' => 'bn', 'options' => ['en' => 'English', 'bn' => 'বাংলা']],
                ],
            ],
            'contact' => [
                'label' => 'Contact & offices',
                'permission' => 'settings.manage',
                'fields' => [
                    'phone' => ['type' => 'text', 'label' => 'Phone', 'default' => '01786-852453', 'rules' => 'required|max:30'],
                    'whatsapp' => ['type' => 'text', 'label' => 'WhatsApp (country code + number, digits only)', 'default' => '8801786852453', 'rules' => 'required|digits_between:10,15'],
                    'email' => ['type' => 'email', 'label' => 'Email', 'default' => 'engr.nomanbabu@gmail.com', 'rules' => 'required|email|max:150'],
                    'office_hours' => self::tr('Office hours', 'Sat – Thu, 10:00 AM – 7:00 PM', 'শনি – বৃহস্পতি, সকাল ১০টা – সন্ধ্যা ৭টা'),
                    'rangpur_address' => self::tr('Rangpur office address', 'House-229, Road-3, Thana Road, Rangpur', 'বাড়ি-২২৯, রোড-৩, থানা রোড, রংপুর'),
                    'dhaka_address' => self::tr('Dhaka office address', 'House-31, Road-10, Block-L, South Banasree, Dhaka', 'বাড়ি-৩১, রোড-১০, ব্লক-এল, দক্ষিণ বনশ্রী, ঢাকা'),
                    'facebook' => ['type' => 'url', 'label' => 'Facebook page URL', 'default' => 'https://www.facebook.com/FriendsDreamhome', 'rules' => 'nullable|url:https|max:255'],
                    'youtube' => ['type' => 'url', 'label' => 'YouTube channel URL', 'default' => '', 'rules' => 'nullable|url:https|max:255'],
                    'linkedin' => ['type' => 'url', 'label' => 'LinkedIn URL', 'default' => '', 'rules' => 'nullable|url:https|max:255'],
                    'notify_email' => ['type' => 'text', 'label' => 'Email new inquiries to (comma-separated, up to 5)', 'default' => 'engr.nomanbabu@gmail.com', 'rules' => ['nullable', 'string', 'max:500', new EmailList]],
                ],
            ],
            'hero' => [
                'label' => 'Home: hero',
                'permission' => 'content.manage',
                'fields' => [
                    'hero_eyebrow' => self::tr('Small heading above the title', 'Rangpur · Dhaka', 'রংপুর · ঢাকা'),
                    'hero_title_start' => self::tr('Title — first part', 'Your home designed, approved and built —', 'আপনার বাড়ির নকশা, অনুমোদন ও নির্মাণ —'),
                    'hero_title_highlight' => self::tr('Title — highlighted words (shown in blue)', 'in Rangpur', 'রংপুরে'),
                    'hero_title_end' => self::tr('Title — last part (optional)', '', ''),
                    'hero_subline' => self::tr('Short line under the title', 'Make your dream with us.', 'আপনার স্বপ্ন গড়ুন আমাদের সাথে।'),
                    'hero_lead' => self::tr('Intro paragraph', 'From architectural drawings to the soil test — one team, supervised by Engr. Noman, BSc in Civil Engineering (IEB).', 'আর্কিটেকচারাল নকশা থেকে সয়েল টেস্ট — সব এক দলে, ইঞ্জি. নোমানের (বিএসসি সিভিল, আইইবি) তত্ত্বাবধানে।', 'textarea'),
                    'hero_cta' => self::tr('Main button text', 'Talk about your project', 'প্রকল্প নিয়ে কথা বলুন'),
                    'hero_image' => ['type' => 'image', 'label' => 'Hero photo — a finished building works best', 'size' => '2100 × 900 px · wide landscape (21:9)', 'default' => ''],
                    'stat_1_value' => self::tr('Stat 1 — number', '11K+', '১১ হাজার+'),
                    'stat_1_label' => self::tr('Stat 1 — label', 'Facebook followers', 'ফেসবুক ফলোয়ার'),
                    'stat_2_value' => self::tr('Stat 2 — number', '2', '২'),
                    'stat_2_label' => self::tr('Stat 2 — label', 'Offices · Rangpur & Dhaka', 'অফিস · রংপুর ও ঢাকা'),
                    'stat_3_value' => self::tr('Stat 3 — number', 'IEB', 'আইইবি'),
                    'stat_3_label' => self::tr('Stat 3 — label', 'Certified civil engineer', 'সনদপ্রাপ্ত সিভিল ইঞ্জিনিয়ার'),
                ],
            ],
            'sections' => [
                'label' => 'Home: section headings',
                'permission' => 'content.manage',
                'fields' => [
                    'services_title' => self::tr('Services — heading', 'What we do', 'আমরা যা করি'),
                    'services_sub' => self::tr('Services — text', 'Five services. Take one, or hand us the whole job.', 'পাঁচটি সেবা — একটা নিন, অথবা পুরো কাজটাই আমাদের দিন।', 'textarea'),
                    'types_title' => self::tr('What we design — heading', 'What we design', 'আমরা যা ডিজাইন করি'),
                    'projects_title' => self::tr('Projects — heading', 'Recent projects', 'সাম্প্রতিক প্রকল্প'),
                    'process_title' => self::tr('Process — heading', 'How the work goes', 'কাজ যেভাবে হয়'),
                    'why_title' => self::tr('Why us — heading', 'The engineer who draws it checks it on site', 'নকশা যিনি করেন, সাইটে তিনিই যান'),
                    'why_sub' => self::tr('Why us — text', 'No chain of middlemen. The same team designs your building, gets it approved and checks the work on site.', 'মাঝে কোনো দালাল নেই। একই দল নকশা করে, অনুমোদন করায়, আর সাইটে গিয়ে কাজ দেখে।', 'textarea'),
                    'team_title' => self::tr('Team — heading', 'The team', 'আমাদের দল'),
                    'testimonials_title' => self::tr('Testimonials — heading', 'Clients', 'ক্লায়েন্টদের কথা'),
                    'faq_title' => self::tr('FAQ — heading', 'Questions', 'প্রশ্ন ও উত্তর'),
                    'contact_title' => self::tr('Contact — heading', 'Tell us about your land', 'আপনার জমির কথা বলুন'),
                    'contact_sub' => self::tr('Contact — text', 'Where it is, how big, how many floors. We’ll call you back — usually the same day.', 'কোথায়, কত বড়, কত তলা — জানান। আমরা সাধারণত একই দিনে কল করি।'),
                    'cta_title' => self::tr('Call-to-action band — heading', 'Planning to build?', 'বাড়ি বানানোর পরিকল্পনা করছেন?'),
                    'cta_sub' => self::tr('Call-to-action band — text', 'Talk to an engineer before you spend on anything.', 'কোনো খরচের আগে একজন ইঞ্জিনিয়ারের সাথে কথা বলুন।'),
                ],
            ],
            'types' => [
                'label' => 'Home: what we design',
                'permission' => 'content.manage',
                'fields' => self::repeat('type', [
                    ['Residential Homes', 'Single & multi-storey family houses', 'আবাসিক বাড়ি', 'একতলা ও বহুতল পারিবারিক বাড়ি'],
                    ['Duplex & Villas', 'Modern elevations, smart layouts', 'ডুপ্লেক্স ও ভিলা', 'আধুনিক এলিভেশন, স্মার্ট লেআউট'],
                    ['Apartment Buildings', 'Structural design to BNBC standards', 'অ্যাপার্টমেন্ট ভবন', 'বিএনবিসি মান অনুযায়ী স্ট্রাকচারাল ডিজাইন'],
                    ['Commercial & Shops', 'Markets, offices, mixed-use', 'বাণিজ্যিক ভবন ও দোকান', 'মার্কেট, অফিস, মিশ্র ব্যবহার'],
                    ['Interior Design', 'Living, bedroom, kitchen & office', 'ইন্টেরিয়র ডিজাইন', 'লিভিং, বেডরুম, কিচেন ও অফিস'],
                    ['Renovation & Extension', 'Vertical extension, redesign', 'সংস্কার ও সম্প্রসারণ', 'ঊর্ধ্বমুখী সম্প্রসারণ, পুনঃনকশা'],
                ]),
            ],
            'process' => [
                'label' => 'Home: work process',
                'permission' => 'content.manage',
                'fields' => self::repeat('process', [
                    ['Consultation', 'We listen to your needs, budget and timeline — and visit your land.', 'পরামর্শ', 'আপনার চাহিদা, বাজেট ও সময়সীমা শুনি — এবং আপনার জমি পরিদর্শন করি।'],
                    ['Survey & Soil Test', 'Digital land survey and soil investigation for a safe foundation.', 'সার্ভে ও সয়েল টেস্ট', 'নিরাপদ ফাউন্ডেশনের জন্য ডিজিটাল ভূমি জরিপ ও মাটি পরীক্ষা।'],
                    ['Design', 'Architectural, structural, interior, electrical & plumbing drawings.', 'ডিজাইন', 'আর্কিটেকচারাল, স্ট্রাকচারাল, ইন্টেরিয়র, ইলেকট্রিক্যাল ও প্লাম্বিং নকশা।'],
                    ['Estimate & Approval', 'Detailed cost estimate and RAJUK / City Corp / Upazila approval sheet.', 'খরচ ও অনুমোদন', 'বিস্তারিত খরচের হিসাব এবং রাজউক / সিটি কর্পোরেশন / উপজেলা অনুমোদন নকশা।'],
                    ['Build & Supervise', 'On-site construction management until your dream home is ready.', 'নির্মাণ ও তত্ত্বাবধান', 'স্বপ্নের বাড়ি তৈরি না হওয়া পর্যন্ত সাইটে নির্মাণ ব্যবস্থাপনা।'],
                ]),
            ],
            'why' => [
                'label' => 'Home: why choose us',
                'permission' => 'content.manage',
                'fields' => self::repeat('why', [
                    ['Safe structures', 'Designs checked by an IEB-certified civil engineer, following BNBC code.', 'নিরাপদ কাঠামো', 'বিএনবিসি কোড অনুযায়ী, আইইবি সনদপ্রাপ্ত সিভিল ইঞ্জিনিয়ার দ্বারা যাচাইকৃত ডিজাইন।'],
                    ['One-stop service', 'Design, approval, survey, estimate and supervision — one responsible team.', 'ওয়ান-স্টপ সেবা', 'ডিজাইন, অনুমোদন, সার্ভে, খরচ নির্ধারণ ও তত্ত্বাবধান — একটি দায়িত্বশীল দল।'],
                    ['Transparent budget', 'Realistic cost estimates up front, so you can plan with confidence.', 'স্বচ্ছ বাজেট', 'শুরুতেই বাস্তবসম্মত খরচের হিসাব, যাতে আপনি নিশ্চিন্তে পরিকল্পনা করতে পারেন।'],
                    ['Local & reachable', 'Offices in Rangpur and Dhaka — we’re a phone call away.', 'কাছেই আছি', 'রংপুর ও ঢাকায় অফিস — একটি ফোন কলেই আমাদের পাবেন।'],
                ]),
            ],
            'about' => [
                'label' => 'About page',
                'permission' => 'content.manage',
                'fields' => [
                    'about_title' => self::tr('Heading', 'An engineering practice from Rangpur', 'রংপুরের একটি ইঞ্জিনিয়ারিং প্রতিষ্ঠান'),
                    'about_lead' => self::tr('Intro line', 'Friends Dream Home designs, gets approval for and supervises buildings for families and businesses in Rangpur, Dhaka and beyond.', 'ফ্রেন্ডস ড্রিম হোম রংপুর, ঢাকা ও আশেপাশে পরিবার ও প্রতিষ্ঠানের জন্য ভবনের নকশা, অনুমোদন ও তত্ত্বাবধানের কাজ করে।', 'textarea'),
                    'about_body' => self::tr('Main text (blank line = new paragraph)', "Friends Dream Home Design & Consultancy (FDH) is a Rangpur-based engineering and design firm with an office in Dhaka. We serve clients across Bangladesh who want their building done right — safe, beautiful, approved and within budget.\n\nWe combine creative architecture with sound structural engineering, and we stay with you through government approval and construction. One team, one point of contact, full responsibility.", "ফ্রেন্ডস ড্রিম হোম ডিজাইন অ্যান্ড কনসালটেন্সি (এফডিএইচ) রংপুর-ভিত্তিক একটি ইঞ্জিনিয়ারিং ও ডিজাইন প্রতিষ্ঠান, ঢাকাতেও আমাদের অফিস রয়েছে। সারা বাংলাদেশে যাঁরা নিরাপদ, সুন্দর, অনুমোদিত এবং বাজেটের মধ্যে ভবন নির্মাণ করতে চান, আমরা তাঁদের সেবা দিই।\n\nসৃজনশীল আর্কিটেকচার ও নির্ভরযোগ্য স্ট্রাকচারাল ইঞ্জিনিয়ারিংয়ের সমন্বয়ে আমরা কাজ করি এবং সরকারি অনুমোদন ও নির্মাণের পুরো সময় আপনার পাশে থাকি। একটি দল, একটি যোগাযোগ, সম্পূর্ণ দায়িত্ব।", 'textarea'),
                    'about_image' => ['type' => 'image', 'label' => 'About page photo', 'size' => '2100 × 900 px · wide landscape (21:9)', 'default' => ''],
                    'md_photo' => ['type' => 'image', 'label' => 'Managing director — portrait photo', 'size' => '800 × 1000 px · portrait (4:5)', 'default' => ''],
                    'md_name' => self::tr('Managing director — name', 'Engr. Noman', 'ইঞ্জি. নোমান'),
                    'md_title' => self::tr('Managing director — title', 'Managing Director · BSc in Civil Engineering (IEB)', 'ব্যবস্থাপনা পরিচালক · বিএসসি ইন সিভিল ইঞ্জিনিয়ারিং (আইইবি)'),
                ],
            ],
            'calculator' => [
                'label' => 'Cost calculator',
                'permission' => 'settings.manage',
                'fields' => [
                    'calc_enabled' => ['type' => 'select', 'label' => 'Show the cost calculator on the website', 'default' => 'no', 'options' => ['no' => 'No — hidden', 'yes' => 'Yes — show it']],
                    'calc_rate_standard' => ['type' => 'text', 'label' => 'Standard finishing — construction cost per sq ft (৳)', 'default' => '', 'rules' => 'nullable|integer|min:100|max:100000'],
                    'calc_rate_premium' => ['type' => 'text', 'label' => 'Premium finishing — cost per sq ft (৳, optional)', 'default' => '', 'rules' => 'nullable|integer|min:100|max:100000'],
                    'calc_rate_luxury' => ['type' => 'text', 'label' => 'Luxury finishing — cost per sq ft (৳, optional)', 'default' => '', 'rules' => 'nullable|integer|min:100|max:100000'],
                    'calc_coverage' => ['type' => 'text', 'label' => 'Typical ground coverage of the plot (%)', 'default' => '60', 'rules' => 'required|integer|min:20|max:100'],
                    'calc_range' => ['type' => 'text', 'label' => 'Show the result as a range of ± (%)', 'default' => '10', 'rules' => 'required|integer|min:0|max:50'],
                    'calc_note' => self::tr('Note under the result', 'This is an approximate construction cost for planning only. Land price, approval, design and soil-test fees, and market price changes are not included. Contact us for an exact estimate (BOQ).', 'এটি শুধু পরিকল্পনার জন্য আনুমানিক নির্মাণ খরচ। জমির দাম, অনুমোদন, ডিজাইন ও সয়েল টেস্ট ফি এবং বাজারদরের পরিবর্তন এতে ধরা হয়নি। সঠিক হিসাবের (বিওকিউ) জন্য আমাদের সাথে যোগাযোগ করুন।', 'textarea'),
                ],
            ],
            'tracking' => [
                'label' => 'Facebook / Meta tracking',
                'permission' => 'settings.manage',
                'fields' => [
                    'meta_pixel_id' => ['type' => 'text', 'label' => 'Meta Pixel ID (Events Manager → Data sources)', 'default' => '', 'rules' => 'nullable|digits_between:10,20'],
                    'meta_capi_token' => ['type' => 'secret', 'label' => 'Conversions API access token (stored encrypted)', 'default' => ''],
                    'meta_test_code' => ['type' => 'text', 'label' => 'Test event code (only while testing in Events Manager)', 'default' => '', 'rules' => 'nullable|alpha_num|max:30'],
                ],
            ],
            'seo' => [
                'label' => 'SEO & footer',
                'permission' => 'settings.manage',
                'fields' => [
                    'meta_title' => self::tr('Home page title (browser tab & Google)', 'Friends Dream Home — Design & Consultancy in Rangpur & Dhaka', 'ফ্রেন্ডস ড্রিম হোম — রংপুর ও ঢাকায় ডিজাইন ও কনসালটেন্সি'),
                    'meta_description' => self::tr('Meta description (Google snippet)', 'Building design, RAJUK / City Corporation / Upazila approval, construction supervision, digital survey, soil test and cost estimation in Rangpur and Dhaka.', 'রংপুর ও ঢাকায় বিল্ডিং ডিজাইন, রাজউক / সিটি কর্পোরেশন / উপজেলা অনুমোদন, নির্মাণ তত্ত্বাবধান, ডিজিটাল সার্ভে, সয়েল টেস্ট ও খরচ নির্ধারণ।', 'textarea'),
                    'og_image' => ['type' => 'image', 'label' => 'Social share image (Facebook preview)', 'size' => '1200 × 630 px · landscape', 'default' => ''],
                    'footer_about' => self::tr('Footer — short about', 'Building design, approval, supervision, survey and cost estimation — from the first sketch to the final brick.', 'বিল্ডিং ডিজাইন, অনুমোদন, তত্ত্বাবধান, সার্ভে ও খরচ নির্ধারণ — প্রথম স্কেচ থেকে শেষ ইট পর্যন্ত।', 'textarea'),
                    'footer_tagline' => self::tr('Footer — slogan', '❤️ Make Your Dream With Us ❤️', '❤️ আমাদের সাথে আপনার স্বপ্ন গড়ুন ❤️'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fields(): array
    {
        return array_merge(...array_values(array_map(fn (array $group): array => $group['fields'], self::groups())));
    }

    /**
     * @return array<string, mixed>
     */
    private static function tr(string $label, string $en, string $bn, string $type = 'text'): array
    {
        return ['type' => $type, 'translatable' => true, 'label' => $label, 'default' => ['en' => $en, 'bn' => $bn]];
    }

    /**
     * Numbered title/text pairs, e.g. process_1_title, process_1_text.
     *
     * @param  list<array{0: string, 1: string, 2: string, 3: string}>  $items
     * @return array<string, array<string, mixed>>
     */
    private static function repeat(string $prefix, array $items): array
    {
        $fields = [];
        foreach ($items as $index => [$titleEn, $textEn, $titleBn, $textBn]) {
            $number = $index + 1;
            $fields["{$prefix}_{$number}_title"] = self::tr("#{$number} title", $titleEn, $titleBn);
            $fields["{$prefix}_{$number}_text"] = self::tr("#{$number} text", $textEn, $textBn);
        }

        return $fields;
    }
}
