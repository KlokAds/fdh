<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect(['home', 'about', 'services', 'projects', 'team', 'faq', 'contact'])->map(fn (string $name): string => route($name));
        $urls = $urls
            ->merge(Service::query()->active()->get()->map(fn (Service $service): string => route('services.show', $service)))
            ->merge(Project::query()->published()->get()->map(fn (Project $project): string => route('projects.show', $project)));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.e($url).'</loc></url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        // The admin path is deliberately not listed here; admin pages send X-Robots-Tag: noindex instead.
        $body = "User-agent: *\nAllow: /\n\nSitemap: ".route('sitemap')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
