<?php

namespace Ginkelsoft\Buildora\Fields\Traits;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Trait SanitizesHtml
 *
 * Sanitizes WYSIWYG/HTML input before it is persisted, so stored values can
 * safely be rendered unescaped (e.g. via `{!! $value !!}`) without exposing
 * the application to stored XSS (OWASP A03:2021 - Injection).
 *
 * Sanitization runs in {@see self::prepareForStorage()}, which the
 * controller calls with the raw request value right before the model is
 * created/updated - i.e. "before save". Note this is a different hook than
 * `setValue()`, which in this package already means "populate `$value` for
 * display from a model" (see Field-subklassen elders). It is enabled by
 * default and can be configured per field instance via `->sanitize()` and
 * `->allowedTags()`.
 */
trait SanitizesHtml
{
    /**
     * Whether HTML sanitization is enabled for this field. Defaults to true
     * ("secure by default" - least privilege for stored HTML).
     *
     * @var bool
     */
    protected bool $sanitizeHtml = true;

    /**
     * The tags (optionally with allowed attributes, using HTMLPurifier's
     * `HTML.Allowed` syntax, e.g. `a[href|title]`) that survive
     * sanitization. Anything not in this list (including <script>,
     * event handlers such as onerror/onclick, and javascript: URIs) is
     * stripped.
     *
     * @var array<int, string>
     */
    protected array $allowedTags = [
        'p', 'br', 'hr',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'code', 'pre',
        'a[href|title|target|rel]',
        'span',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    /**
     * Enable or disable HTML sanitization for this field.
     *
     * Disabling this is opt-in and puts the responsibility for safe
     * rendering back on the developer - only do this when the value is
     * fully trusted (e.g. seeded by an admin, never end-user input).
     *
     * @param bool $enabled
     * @return static
     */
    public function sanitize(bool $enabled = true): static
    {
        $this->sanitizeHtml = $enabled;
        return $this;
    }

    /**
     * Configure the allow-list of tags (and attributes) kept after
     * sanitization. Uses HTMLPurifier's `HTML.Allowed` syntax, e.g.
     * `['p', 'a[href|title]', 'img[src|alt]']`.
     *
     * @param array<int, string> $tags
     * @return static
     */
    public function allowedTags(array $tags): static
    {
        $this->allowedTags = $tags;
        return $this;
    }

    /**
     * Get the currently configured allow-list of tags.
     *
     * @return array<int, string>
     */
    public function getAllowedTags(): array
    {
        return $this->allowedTags;
    }

    /**
     * Determine whether sanitization is currently enabled.
     *
     * @return bool
     */
    public function isSanitizeEnabled(): bool
    {
        return $this->sanitizeHtml;
    }

    /**
     * Transform a raw incoming value before it is persisted.
     *
     * Called by the controller with the request value for this field right
     * before create()/update(), so malicious markup (e.g.
     * `<script>alert(1)</script>`) never reaches the database.
     *
     * @param mixed $value
     * @return mixed
     */
    public function prepareForStorage(mixed $value): mixed
    {
        if (! $this->sanitizeHtml || ! is_string($value) || $value === '') {
            return $value;
        }

        return $this->purifyHtml($value);
    }

    /**
     * Run the value through HTMLPurifier using the configured allow-list.
     *
     * @param string $html
     * @return string
     */
    protected function purifyHtml(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', implode(',', $this->allowedTags));
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        // Geen definitie-cache op schijf nodig (en dus geen schrijfbare map vereist).
        $config->set('Cache.DefinitionImpl', null);

        return (new HTMLPurifier($config))->purify($html);
    }
}
