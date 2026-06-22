<?php

namespace App\Services;

class HtmlSanitizerService
{
    public function sanitize(string $html): string
    {
        $allowedTags = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><em><i><u><a><ul><ol><li><blockquote><code><pre><img><hr>';
        $clean = strip_tags($html, $allowedTags);

        // Strip common event handlers and javascript: URLs.
        $clean = preg_replace('/\son\w+\s*=\s*"[^"]*"/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\son\w+\s*=\s*\'[^\']*\'/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\son\w+\s*=\s*[^\s>]+/i', '', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*"\s*javascript:[^"]*"/i', '$1="#"', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*\'\s*javascript:[^\']*\'/i', '$1="#"', $clean) ?? $clean;

        return $clean;
    }
}
