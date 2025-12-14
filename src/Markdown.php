<?php

declare(strict_types=1);

namespace App;

use ParsedownExtra;

class Markdown
{
    private ParsedownExtra $parsedown;

    public function __construct()
    {
        $this->parsedown = new ParsedownExtra();
        $this->parsedown->setSafeMode(true);
    }

    /**
     * Parse markdown to HTML
     */
    public function parse(string $markdown): string
    {
        return $this->parsedown->text($markdown);
    }

    /**
     * Rewrite internal .md links to clean URLs
     * Also adds target="_blank" to external links
     */
    public function rewriteLinks(string $html, string $currentPath, bool $isIndex = false): string
    {
        // Get the directory of the current document for relative path resolution
        // If the document is an index file (directory landing page), use the path itself as the base
        if ($isIndex) {
            $currentDir = $currentPath;
        } else {
            $currentDir = dirname($currentPath);
            if ($currentDir === '.') {
                $currentDir = '';
            }
        }

        // Rewrite internal .md links
        $html = preg_replace_callback(
            '/<a\s+([^>]*?)href=["\']([^"\']+)["\']([^>]*)>/i',
            function ($matches) use ($currentDir) {
                $before = $matches[1];
                $href = $matches[2];
                $after = $matches[3];

                // External links: add target and rel
                if (preg_match('/^https?:\/\//i', $href)) {
                    return '<a ' . $before . 'href="' . $href . '"' . $after . ' target="_blank" rel="noopener noreferrer">';
                }

                // Internal .md links: convert to clean URLs
                if (preg_match('/\.md(#.*)?$/i', $href)) {
                    $newHref = $this->resolveInternalLink($href, $currentDir);
                    return '<a ' . $before . 'href="' . $newHref . '"' . $after . '>';
                }

                // Leave other links as-is
                return $matches[0];
            },
            $html
        );

        return $html;
    }

    /**
     * Resolve an internal .md link to a clean URL
     */
    private function resolveInternalLink(string $href, string $currentDir): string
    {
        // Separate anchor from path
        $anchor = '';
        if (($pos = strpos($href, '#')) !== false) {
            $anchor = substr($href, $pos);
            $href = substr($href, 0, $pos);
        }

        // Remove .md extension
        $href = preg_replace('/\.md$/i', '', $href);

        // Resolve relative path
        if (str_starts_with($href, './')) {
            $href = substr($href, 2);
        }

        // Handle relative paths
        if (!str_starts_with($href, '/')) {
            if (!empty($currentDir)) {
                $href = $currentDir . '/' . $href;
            }

            // Normalize path (resolve .. and .)
            $parts = explode('/', $href);
            $normalized = [];

            foreach ($parts as $part) {
                if ($part === '..') {
                    array_pop($normalized);
                } elseif ($part !== '.' && $part !== '') {
                    $normalized[] = $part;
                }
            }

            $href = implode('/', $normalized);
        }

        // Strip numeric prefixes from path segments
        $parts = explode('/', $href);
        $parts = array_map(fn($p) => stripPrefix($p), $parts);
        $href = implode('/', $parts);

        // Build final URL
        $url = '/docs/' . $href;

        // Handle index pages
        if (str_ends_with($url, '/index')) {
            $url = substr($url, 0, -6);
        }

        return $url . $anchor;
    }

    /**
     * Add anchor IDs to headings
     */
    public function addHeadingAnchors(string $html): string
    {
        return preg_replace_callback(
            '/<(h[1-6])>(.+?)<\/\1>/i',
            function ($matches) {
                $tag = $matches[1];
                $text = $matches[2];
                $id = $this->slugify(strip_tags($text));

                return '<' . $tag . ' id="' . $id . '">' .
                    $text .
                    ' <a href="#' . $id . '" class="heading-anchor">#</a>' .
                    '</' . $tag . '>';
            },
            $html
        );
    }

    /**
     * Extract headings for table of contents
     */
    public function extractHeadings(string $html): array
    {
        $headings = [];

        preg_match_all('/<(h[2-4])\s+id=["\']([^"\']+)["\']>.*?<\/\1>/i', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $level = (int) substr($match[1], 1);
            $id = $match[2];
            // Extract text content, removing the anchor link
            $text = strip_tags(preg_replace('/<a[^>]*class=["\']heading-anchor["\'][^>]*>.*?<\/a>/i', '', $match[0]));

            $headings[] = [
                'level' => $level,
                'id' => $id,
                'text' => trim($text),
            ];
        }

        return $headings;
    }

    /**
     * Convert text to URL-safe slug
     */
    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
