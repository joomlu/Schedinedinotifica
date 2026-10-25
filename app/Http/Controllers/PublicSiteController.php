<?php

namespace App\Http\Controllers;

use App\Support\PublicSiteRenderer;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicSiteController extends Controller
{
    public function home(): Response
    {
        return $this->page('index');
    }

    public function page(string $page): Response
    {
        abort_unless(array_key_exists($page, PublicSiteRenderer::PAGES), 404);

        $html = file_get_contents(base_path('web/'.$page.'.html'));

        return response()->make(app(PublicSiteRenderer::class)->render($html), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache',
            'X-Sito-Versione' => app(PublicSiteRenderer::class)->version(),
        ]);
    }

    public function asset(string $path): BinaryFileResponse
    {
        $versioned = false;
        if (preg_match('/^(app|styles)\.([a-f0-9]{16})\.(js|css)$/D', $path, $match)) {
            $canonical = $match[1].'.'.$match[3];
            abort_unless(in_array($canonical, ['app.js', 'styles.css'], true), 404);
            abort_unless(hash_equals(substr(hash_file('sha256', base_path('web/assets/'.$canonical)), 0, 16), $match[2]), 404);
            $path = $canonical;
            $versioned = true;
        }
        $assetsRoot = realpath(base_path('web/assets'));
        $assetPath = realpath(base_path('web/assets/'.ltrim($path, '/')));

        abort_unless(
            $assetsRoot !== false
            && $assetPath !== false
            && is_file($assetPath)
            && str_starts_with($assetPath, $assetsRoot.DIRECTORY_SEPARATOR),
            404
        );

        $headers = ['Cache-Control' => $versioned ? 'public, max-age=31536000, immutable' : 'no-cache'];
        $mime = ['css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json'][pathinfo($assetPath, PATHINFO_EXTENSION)] ?? null;
        if ($mime !== null) {
            $headers['Content-Type'] = $mime.'; charset=UTF-8';
        }

        return response()->file($assetPath, $headers);
    }
}
