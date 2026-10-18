<?php

namespace App\Services;

use App\Models\Struttura;
use Illuminate\Support\Facades\Storage;

class WebCheckinIdentita
{
    public function of(Struttura $s): array
    {
        $logo = null;
        $relative = (string) $s->logo;
        // Solo immagini locali della struttura: niente URL remoti, traversal o SVG attivi.
        if (preg_match('~\Astorage/uploads/loghi/[a-zA-Z0-9_.-]+\z~D', $relative)) {
            $disk = Storage::disk('public');
            $path = realpath($disk->path(substr($relative, strlen('storage/'))));
            $root = realpath($disk->path('uploads/loghi'));
            if ($path && $root && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path)
                && in_array(mime_content_type($path), ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)) $logo = $path;
        }
        $width = $height = null;
        if ($logo) {
            $dimensions = @getimagesize($logo);
            if (! $dimensions || $dimensions[0] < 1 || $dimensions[1] < 1) $logo = null;
            else { $scale = min(220 / $dimensions[0], 90 / $dimensions[1]); $width = max(1, (int) round($dimensions[0] * $scale)); $height = max(1, (int) round($dimensions[1] * $scale)); }
        }
        $color = preg_match('/\A#[a-fA-F0-9]{6}\z/D', (string) $s->web_checkin_colore) ? $s->web_checkin_colore : '#245563';
        $rgb = array_map(fn ($p) => hexdec($p) / 255, str_split(substr($color, 1), 2));
        $rgb = array_map(fn ($n) => $n <= .04045 ? $n / 12.92 : (($n + .055) / 1.055) ** 2.4, $rgb);
        $luminance = .2126 * $rgb[0] + .7152 * $rgb[1] + .0722 * $rgb[2];

        return ['nome' => $s->nome_struttura, 'logo_path' => $logo,
            'logo_url' => $logo ? rtrim(config('app.url'), '/').'/'.$relative : null,
            'logo_sha256' => $logo ? hash_file('sha256', $logo) : null, 'logo_width' => $width, 'logo_height' => $height,
            'colore' => $color, 'testo_colore' => 1.05 / ($luminance + .05) >= 4.5 ? '#ffffff' : '#142025',
            'email' => filter_var($s->email, FILTER_VALIDATE_EMAIL) ? $s->email : null,
            'telefono' => $s->telefono];
    }
}
