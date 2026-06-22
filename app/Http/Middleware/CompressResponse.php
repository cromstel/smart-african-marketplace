<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldCompress($request, $response)) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '') {
            return $response;
        }

        $compressed = gzencode($content, 6);
        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Vary', trim(($response->headers->get('Vary') ? $response->headers->get('Vary').', ' : '').'Accept-Encoding'));
        $response->headers->set('Content-Length', (string) strlen($compressed));

        return $response;
    }

    private function shouldCompress(Request $request, Response $response): bool
    {
        if (! str_contains((string) $request->header('Accept-Encoding'), 'gzip')) {
            return false;
        }

        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        $compressible = str_contains($contentType, 'text/')
            || str_contains($contentType, 'application/json')
            || str_contains($contentType, 'application/javascript')
            || str_contains($contentType, 'application/xml')
            || str_contains($contentType, 'image/svg+xml');

        if (! $compressible) {
            return false;
        }

        $content = $response->getContent();
        if (! is_string($content) || strlen($content) < 1024) {
            return false;
        }

        return true;
    }
}
