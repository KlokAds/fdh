<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

/**
 * Formatted text from the admin editor. Only a small set of tags survives; every attribute
 * except a safe link href is dropped, so nothing written in the editor can run script.
 */
class RichText
{
    /** Tags kept as they are. */
    private const TAGS = ['p', 'br', 'strong', 'em', 'u', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'a'];

    /** Classes an editor photo may carry: one size and one position. */
    private const IMAGE_SIZES = ['rt-sm', 'rt-md', 'rt-lg', 'rt-full'];

    private const IMAGE_ALIGN = ['rt-left', 'rt-right', 'rt-center'];

    /** Tags renamed to their kept equivalent. */
    private const ALIASES = ['b' => 'strong', 'i' => 'em', 'div' => 'p', 'h1' => 'h2', 'h4' => 'h3', 'h5' => 'h3', 'h6' => 'h3'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        if (! self::isHtml($html)) {
            $html = self::fromPlainText($html);
        } elseif (! preg_match('#<(p|h[1-6]|ul|ol|blockquote|div)\b#i', $html)) {
            // Inline formatting only: blank lines still mark paragraphs.
            $html = self::paragraphs($html, fn (string $paragraph): string => $paragraph);
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="rt-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('rt-root');
        if (! $root) {
            return '';
        }

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= self::renderNode($child);
        }

        // Drop empty paragraphs the editor leaves behind.
        $output = preg_replace('#<p>(\s|&nbsp;|<br>)*</p>#u', '', $output) ?? '';

        return trim($output);
    }

    /**
     * Safe HTML for a page: formatted text is cleaned, older plain text becomes paragraphs.
     */
    public static function render(?string $value): HtmlString
    {
        return new HtmlString(self::clean($value));
    }

    /**
     * Plain-text version (for previews, meta descriptions and the character count).
     */
    public static function plain(?string $value): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>', '</h2>', '</h3>'], ["</p>\n", "\n", "</li>\n", "</h2>\n", "</h3>\n"], (string) $value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace("/\n{3,}/", "\n\n", $text) ?? '');
    }

    public static function isHtml(string $value): bool
    {
        return (bool) preg_match('#</?(p|br|strong|b|em|i|u|h[1-6]|ul|ol|li|blockquote|a|div|img)\b#i', $value);
    }

    /**
     * Paths of editor photos used in a piece of formatted text (for the media library).
     *
     * @return list<string>
     */
    public static function imagePaths(?string $html): array
    {
        preg_match_all('#/storage/(editor/[A-Za-z0-9]{40}\.webp)#', (string) $html, $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * Only photos uploaded through the editor are allowed; the URL is rebuilt from the file name.
     */
    private static function renderImage(DOMElement $node): string
    {
        if (! preg_match('#/storage/editor/([A-Za-z0-9]{40}\.webp)(?:[?\#].*)?$#', $node->getAttribute('src'), $match)) {
            return '';
        }

        $classes = preg_split('/\s+/', $node->getAttribute('class')) ?: [];
        $size = array_values(array_intersect($classes, self::IMAGE_SIZES))[0] ?? 'rt-md';
        $align = array_values(array_intersect($classes, self::IMAGE_ALIGN))[0] ?? 'rt-center';
        $url = Storage::disk('public')->url('editor/'.$match[1]);

        return '<img src="'.e($url).'" alt="'.e(mb_substr($node->getAttribute('alt'), 0, 150)).'" class="rt-img '.$size.' '.$align.'" loading="lazy">';
    }

    private static function fromPlainText(string $text): string
    {
        return self::paragraphs($text, fn (string $paragraph): string => e($paragraph));
    }

    /**
     * Blank lines start a paragraph, single line breaks become <br>.
     *
     * @param  callable(string): string  $prepare
     */
    private static function paragraphs(string $text, callable $prepare): string
    {
        $paragraphs = preg_split('/\n\s*\n/', str_replace("\r\n", "\n", $text)) ?: [];

        return implode('', array_map(
            fn (string $paragraph): string => '<p>'.str_replace("\n", '<br>', $prepare(trim($paragraph))).'</p>',
            array_filter($paragraphs, fn (string $paragraph): bool => trim($paragraph) !== ''),
        ));
    }

    private static function renderNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return e($node->textContent);
        }

        if (! $node instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'template', 'svg', 'math'], true)) {
            return '';
        }

        if ($tag === 'img') {
            return self::renderImage($node);
        }

        $tag = self::ALIASES[$tag] ?? $tag;
        $inner = '';
        foreach (iterator_to_array($node->childNodes) as $child) {
            $inner .= self::renderNode($child);
        }

        if (! in_array($tag, self::TAGS, true)) {
            return $inner;
        }

        if ($tag === 'br') {
            return '<br>';
        }

        if ($tag === 'a') {
            $href = trim($node->getAttribute('href'));
            if (! preg_match('#^(https?://|mailto:|tel:)#i', $href)) {
                return $inner;
            }
            $external = str_starts_with(strtolower($href), 'http');

            return '<a href="'.e($href).'"'.($external ? ' target="_blank" rel="noopener noreferrer nofollow"' : '').'>'.$inner.'</a>';
        }

        return "<{$tag}>{$inner}</{$tag}>";
    }
}
