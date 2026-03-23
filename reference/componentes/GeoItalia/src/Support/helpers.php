<?php

if (!function_exists('geoitalia_path')) {
    function geoitalia_path(string $path = ''): string
    {
        $base = __DIR__ . '/..';
        return rtrim($base . '/' . ltrim($path, '/'), '/');
    }
}
