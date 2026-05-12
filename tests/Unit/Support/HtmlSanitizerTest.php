<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Support;

use Ginkelsoft\Buildora\Support\HtmlSanitizer;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class HtmlSanitizerTest extends TestCase
{
    #[Test]
    public function it_returns_empty_string_for_null_or_empty_input(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean(''));
    }

    #[Test]
    public function it_preserves_safe_formatting_tags(): void
    {
        $input = '<p>Hello <strong>world</strong> and <em>everyone</em>.</p>';

        $output = HtmlSanitizer::clean($input);

        $this->assertStringContainsString('<p>', $output);
        $this->assertStringContainsString('<strong>', $output);
        $this->assertStringContainsString('<em>', $output);
        $this->assertStringContainsString('Hello', $output);
    }

    #[Test]
    public function it_strips_script_tags_entirely(): void
    {
        $payload = '<p>Hi</p><script>alert("xss")</script>';

        $output = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('alert', $output);
        $this->assertStringContainsString('Hi', $output);
    }

    #[Test]
    public function it_strips_iframe_object_embed_style_link_meta(): void
    {
        $payload = '<iframe src="x"></iframe><object></object><embed><style>body{}</style><link rel="x"><meta charset="utf-8">';

        $output = HtmlSanitizer::clean($payload);

        foreach (['<iframe', '<object', '<embed', '<style', '<link', '<meta'] as $tag) {
            $this->assertStringNotContainsString($tag, $output, "Did not expect to find {$tag} in output.");
        }
    }

    #[Test]
    public function it_strips_event_handler_attributes(): void
    {
        $payload = '<p onclick="alert(1)" onmouseover="boom()">Hi</p>';

        $output = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('onclick', $output);
        $this->assertStringNotContainsString('onmouseover', $output);
        $this->assertStringContainsString('Hi', $output);
    }

    #[Test]
    public function it_strips_img_onerror_payload(): void
    {
        $payload = '<img src="x" onerror="alert(1)">';

        $output = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('onerror', $output);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function dangerousUrls(): array
    {
        return [
            'javascript scheme' => ['javascript:alert(1)'],
            'data url'          => ['data:text/html,<script>alert(1)</script>'],
            'vbscript scheme'   => ['vbscript:msgbox("x")'],
        ];
    }

    #[Test]
    #[DataProvider('dangerousUrls')]
    public function it_drops_href_with_a_dangerous_url_scheme(string $url): void
    {
        $payload = '<a href="' . $url . '">click me</a>';

        $output = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('href=', $output);
        $this->assertStringContainsString('click me', $output);
    }

    #[Test]
    public function it_keeps_safe_http_https_mailto_tel_links(): void
    {
        $payload = implode('', [
            '<a href="https://example.com">a</a>',
            '<a href="http://example.com">b</a>',
            '<a href="mailto:test@example.com">c</a>',
            '<a href="tel:+31123">d</a>',
            '<a href="/relative">e</a>',
            '<a href="#anchor">f</a>',
        ]);

        $output = HtmlSanitizer::clean($payload);

        $this->assertStringContainsString('https://example.com', $output);
        $this->assertStringContainsString('http://example.com', $output);
        $this->assertStringContainsString('mailto:test@example.com', $output);
        $this->assertStringContainsString('tel:+31123', $output);
        $this->assertStringContainsString('/relative', $output);
        $this->assertStringContainsString('#anchor', $output);
    }

    #[Test]
    public function it_unwraps_unknown_tags_but_keeps_text_content(): void
    {
        $payload = '<custom-element>important text</custom-element>';

        $output = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('<custom-element', $output);
        $this->assertStringContainsString('important text', $output);
    }

    #[Test]
    public function it_strips_attributes_not_on_the_per_tag_whitelist(): void
    {
        // 'p' has no allowed attributes, so style/class/id should be stripped.
        $payload = '<p class="bad" id="x" style="background:red">hi</p>';

        $output = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('class=', $output);
        $this->assertStringNotContainsString('id=', $output);
        $this->assertStringNotContainsString('style=', $output);
        $this->assertStringContainsString('hi', $output);
    }

    #[Test]
    public function it_allows_overriding_the_whitelist(): void
    {
        $payload = '<p>nope</p><strong>nope</strong><em>yes</em>';

        $output = HtmlSanitizer::clean($payload, ['em' => []]);

        $this->assertStringNotContainsString('<p', $output);
        $this->assertStringNotContainsString('<strong', $output);
        $this->assertStringContainsString('<em>yes</em>', $output);
    }

    #[Test]
    public function nested_script_inside_safe_tag_is_stripped(): void
    {
        $payload = '<p>before <script>alert(1)</script> after</p>';

        $output = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('alert', $output);
        $this->assertStringContainsString('before', $output);
        $this->assertStringContainsString('after', $output);
    }
}
