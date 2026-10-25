<?php

namespace App\Http\Controllers;

use App\Support\PublicSiteCatalog;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicSiteController extends Controller
{
    /**
     * @var string[]
     */
    private array $allowedPages = [
        'index',
        'moduli',
        'demo',
        'prezzi',
        'contatti',
    ];

    public function home(): Response
    {
        return $this->page('index');
    }

    public function page(string $page): Response
    {
        abort_unless(in_array($page, $this->allowedPages, true), 404);

        $html = file_get_contents(base_path('web/' . $page . '.html'));

        return response()->make(app(PublicSiteCatalog::class)->render($html), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function asset(string $path): BinaryFileResponse
    {
        $assetsRoot = realpath(base_path('web/assets'));
        $assetPath = realpath(base_path('web/assets/' . ltrim($path, '/')));

        abort_unless(
            $assetsRoot !== false
            && $assetPath !== false
            && is_file($assetPath)
            && str_starts_with($assetPath, $assetsRoot . DIRECTORY_SEPARATOR),
            404
        );

        return response()->file($assetPath, [
            'Cache-Control' => $path === 'catalogo-commerciale.json' ? 'no-cache' : 'public, max-age=3600',
        ]);
    }
}
