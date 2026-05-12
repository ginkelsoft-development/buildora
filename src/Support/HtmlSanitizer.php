<?php

namespace Ginkelsoft\Buildora\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Minimal allow-list-based HTML sanitiser for rich-text output.
 *
 * Buildora's RichTextField renders WYSIWYG content directly into the page,
 * so any HTML stored against the model needs to be cleaned before it reaches
 * the browser — otherwise a stored <script> tag (or an attribute payload
 * like onerror="…" / href="javascript:…") becomes stored XSS.
 *
 * Implementation notes:
 *   - Pure PHP, no external dependency (no HTMLPurifier).
 *   - Strips any tag not in the whitelist; strips any attribute not in the
 *     per-tag whitelist.
 *   - Refuses href/src URLs that use a non-http(s)/mailto/tel scheme so that
 *     `javascript:` and `data:` payloads cannot survive.
 *   - Strips on* event-handler attributes unconditionally.
 *
 * Anything stricter (a full security boundary) should use a vetted library
 * like HTMLPurifier — but this class closes the most common attack paths
 * without adding a heavy dep.
 */
final class HtmlSanitizer
{
    /**
     * @var array<string, array<int, string>>
     *   Map of allowed tag => list of allowed attributes on that tag.
     */
    private const DEFAULT_WHITELIST = [
        'p'          => [],
        'br'         => [],
        'strong'     => [],
        'b'          => [],
        'em'         => [],
        'i'          => [],
        'u'          => [],
        's'          => [],
        'sub'        => [],
        'sup'        => [],
        'h1'         => [],
        'h2'         => [],
        'h3'         => [],
        'h4'         => [],
        'h5'         => [],
        'h6'         => [],
        'ul'         => [],
        'ol'         => [],
        'li'         => [],
        'blockquote' => [],
        'pre'        => [],
        'code'       => [],
        'a'          => ['href', 'title', 'rel', 'target'],
        'img'        => ['src', 'alt', 'title'],
        'hr'         => [],
        'span'       => [],
        'div'        => [],
        'figure'     => [],
        'figcaption' => [],
        'table'      => [],
        'thead'      => [],
        'tbody'      => [],
        'tr'         => [],
        'th'         => [],
        'td'         => [],
    ];

    private const ALLOWED_URL_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /**
     * Clean an HTML string and return the sanitised result.
     *
     * @param array<string, array<int, string>>|null $whitelist Optional override of the tag/attribute whitelist.
     */
    public static function clean(?string $html, ?array $whitelist = null): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $whitelist = $whitelist ?? self::DEFAULT_WHITELIST;

        // Wrap in a fake root so DOMDocument doesn't add <html><body> wrappers
        // and so that we can iterate over multiple top-level nodes.
        $wrapped = '<?xml encoding="UTF-8"?><buildora-root>' . $html . '</buildora-root>';

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementsByTagName('buildora-root')->item(0);
        if ($root === null) {
            return '';
        }

        self::sanitiseNode($root, $whitelist);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }

    /**
     * @param array<string, array<int, string>> $whitelist
     */
    private static function sanitiseNode(DOMNode $node, array $whitelist): void
    {
        // Walk children in reverse so removals don't shift the index.
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach (array_reverse($children) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if (! $child instanceof DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (! isset($whitelist[$tag])) {
                self::unwrap($child);
                continue;
            }

            self::stripDisallowedAttributes($child, $whitelist[$tag]);
            self::sanitiseNode($child, $whitelist);
        }
    }

    /**
     * Replace a disallowed element with its (already-sanitised) children.
     */
    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }

        // For tags whose content we don't trust at all (script/style/iframe),
        // drop the entire subtree.
        $danger = ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta'];
        if (in_array(strtolower($element->nodeName), $danger, true)) {
            $parent->removeChild($element);
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }

    /**
     * @param array<int, string> $allowedAttributes
     */
    private static function stripDisallowedAttributes(DOMElement $element, array $allowedAttributes): void
    {
        $remove = [];
        foreach ($element->attributes as $attr) {
            $name = strtolower($attr->nodeName);

            // Always drop event handlers and any other on* attribute.
            if (str_starts_with($name, 'on')) {
                $remove[] = $attr->nodeName;
                continue;
            }

            if (! in_array($name, $allowedAttributes, true)) {
                $remove[] = $attr->nodeName;
                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! self::isAllowedUrl((string) $attr->nodeValue)) {
                $remove[] = $attr->nodeName;
            }
        }

        foreach ($remove as $name) {
            $element->removeAttribute($name);
        }
    }

    private static function isAllowedUrl(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        // Relative URLs (no scheme) are fine — same-origin.
        if ($value[0] === '/' || $value[0] === '#' || $value[0] === '?') {
            return true;
        }

        if (! preg_match('#^([a-z][a-z0-9+.\-]*):#i', $value, $match)) {
            // No scheme — treat as relative.
            return true;
        }

        return in_array(strtolower($match[1]), self::ALLOWED_URL_SCHEMES, true);
    }
}
