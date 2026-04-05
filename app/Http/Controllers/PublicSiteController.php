<?php

namespace App\Http\Controllers;

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

    public function home(): BinaryFileResponse
    {
        return $this->page('index');
    }

    public function page(string $page): BinaryFileResponse
    {
        abort_unless(in_array($page, $this->allowedPages, true), 404);

        return response()->file(
            base_path('web/' . $page . '.html'),
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
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
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
