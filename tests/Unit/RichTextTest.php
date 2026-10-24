<?php

namespace Tests\Unit;

use App\Support\RichText;
use Tests\TestCase;

class RichTextTest extends TestCase
{
    public function test_formatting_is_kept_and_scripts_are_removed(): void
    {
        $html = RichText::clean('<h2 style="color:red">Plan</h2><p onclick="x()">A <b>bold</b> <script>alert(1)</script>move</p><ul><li>One</li></ul><img src=x onerror=alert(1)>');

        $this->assertSame('<h2>Plan</h2><p>A <strong>bold</strong> move</p><ul><li>One</li></ul>', $html);
    }

    public function test_only_safe_links_survive(): void
    {
        $this->assertSame('<p>Call</p>', RichText::clean('<p><a href="javascript:alert(1)">Call</a></p>'));
        $this->assertSame('<p><a href="tel:01786852453">Call</a></p>', RichText::clean('<p><a href="tel:01786852453">Call</a></p>'));
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', RichText::clean('<a href="https://example.com">x</a>'));
    }

    public function test_only_editor_photos_are_kept_with_allowed_classes(): void
    {
        $name = str_repeat('a', 40).'.webp';
        $html = RichText::clean('<p><img src="https://evil.test/x.png"><img src="/storage/editor/'.$name.'" class="rt-sm rt-left evil" onerror="x()"></p>');

        $this->assertStringNotContainsString('evil', $html);
        $this->assertStringContainsString('class="rt-img rt-sm rt-left"', $html);
        $this->assertSame(['editor/'.$name], RichText::imagePaths($html));
    }

    public function test_older_plain_text_becomes_paragraphs(): void
    {
        $this->assertSame('<p>First line &amp; more</p><p>Second<br>line</p>', RichText::clean("First line & more\n\nSecond\nline"));
        $this->assertSame('<p>First <strong>bold</strong></p><p>Second</p>', RichText::clean("First <b>bold</b>\n\nSecond"));
        $this->assertSame('<p>দোতলা বাড়ি</p>', RichText::clean('<p>দোতলা বাড়ি</p>'));
    }
}
