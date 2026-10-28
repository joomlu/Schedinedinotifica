<?php

namespace App\Support;

final class PublicSiteRenderer
{
    public const PAGES = ['index' => 'site.home', 'moduli' => 'site.moduli', 'ampliamenti' => 'site.ampliamenti', 'demo' => 'site.demo', 'prezzi' => 'site.prezzi', 'contatti' => 'site.contatti', 'note-legali' => 'site.note-legali', 'privacy' => 'site.privacy', 'cookie' => 'site.cookie'];

    public function render(string $html): string
    {
        $html = app(PublicSiteCatalog::class)->render($html);
        $html = $this->selectContactReason($html);
        if (str_contains($html, 'data-demo-form')) {
            $token = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html = preg_replace('~(<form\b[^>]*data-demo-form[^>]*>)~i', '$1<input type="hidden" name="_token" value="'.$token.'">', $html);
        }
        $feedback = '';
        if (request()->hasSession()) {
            if (request()->session()->get('sito-richiesta-registrata') === true) {
                $feedback = '<p data-contact-feedback class="form-status is-success" role="status">Richiesta registrata. Grazie per averci contattato.</p>';
            } elseif ($errors = request()->session()->get('errors')) {
                $feedback = '<p data-contact-feedback class="form-status is-error" role="alert">La richiesta non è stata registrata. '.htmlspecialchars((string) $errors->first(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p>';
            }
        }
        $html = $this->restoreContactInput(str_replace('{{contact.feedback}}', $feedback, $html));
        $routes = ['/' => 'site.home', '/moduli' => 'site.moduli', '/ampliamenti' => 'site.ampliamenti', '/demo' => 'site.demo', '/prezzi' => 'site.prezzi', '/contatti' => 'site.contatti', '/contatti/richiesta' => 'site.contatti.store', '/note-legali' => 'site.note-legali', '/privacy' => 'site.privacy', '/cookie' => 'site.cookie', '/login' => 'login'];
        $html = preg_replace_callback('~\b(href|action)=([\"\'])(/[^\"\']*)\2~i', function (array $m) use ($routes): string {
            preg_match('~^([^?#]*)(.*)$~s', $m[3], $parts);
            $path = $parts[1];

            return $m[1].'='.$m[2].(isset($routes[$path]) ? route($routes[$path], [], false).$parts[2] : $m[3]).$m[2];
        }, $html);
        foreach (['app.js', 'styles.css'] as $asset) {
            [$name, $extension] = explode('.', $asset);
            $version = substr(hash_file('sha256', base_path('web/assets/'.$asset)), 0, 16);
            $html = str_replace('/site-assets/'.$asset, '/site-assets/'.$name.'.'.$version.'.'.$extension, $html);
        }

        return str_replace('</head>', '<meta name="sito-versione" content="'.$this->version().'">'."\n  </head>", $html);
    }

    private function selectContactReason(string $html): string
    {
        if (! str_contains($html, 'data-commercial-contact')) {
            return $html;
        }
        $reasons = ['demo' => 'Richiesta demo', 'gruppo' => 'Presentazione di gruppo', 'informazioni' => 'Informazioni e personalizzazioni'];
        $old = request()->hasSession() ? request()->session()->getOldInput() : [];
        $query = request()->query('motivo');
        $selected = $old['topic'] ?? (is_string($query) ? ($reasons[$query] ?? 'Richiesta demo') : 'Richiesta demo');
        if (! is_string($selected)) {
            $selected = 'Richiesta demo';
        }
        $html = preg_replace_callback('~<select\b[^>]*name="topic"[^>]*>.*?</select>~si', static function (array $m) use ($selected): string {
            return preg_replace_callback('~<option\b[^>]*>~i', static function (array $option) use ($selected): string {
                $tag = preg_replace('~\sselected(?:="[^"]*")?~i', '', $option[0]);
                if (preg_match('~\bvalue="([^"]*)"~', $tag, $value) && $value[1] === $selected) {
                    return substr($tag, 0, -1).' selected>';
                }
                return $tag;
            }, $m[0]);
        }, $html);
        if ($selected !== 'Richiesta demo') {
            $html = str_replace('data-demo-preference>', 'data-demo-preference hidden>', $html);
        }
        if ($selected === 'Presentazione di gruppo') {
            $html = str_replace('data-group-hint hidden>', 'data-group-hint>', $html);
        }
        return $html;
    }

    private function restoreContactInput(string $html): string
    {
        $old = request()->hasSession() ? request()->session()->getOldInput() : [];
        if ($old === [] || ! str_contains($html, 'data-demo-form')) {
            return $html;
        }
        $escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $textFields = ['company', 'name', 'email', 'contact_person', 'city', 'phone', 'mobile', 'website_url', 'contact_time'];
        $checks = ['max_full', 'consent', 'contact_anytime'];
        $html = preg_replace_callback('~<input\b[^>]*>~i', function (array $m) use ($old, $escape, $textFields, $checks): string {
            if (! preg_match('~\bname="([^"]+)"~', $m[0], $name)) {
                return $m[0];
            }
            $key = $name[1];
            if (in_array($key, $checks, true)) {
                $tag = preg_replace('~\schecked(?:="[^"]*")?~i', '', $m[0]);

                return in_array($old[$key] ?? null, [true, 1, '1', 'on', 'yes'], true) ? preg_replace('~\s*/?>$~', ' checked>', $tag) : $tag;
            }
            if (! in_array($key, $textFields, true) || ! array_key_exists($key, $old) || ! is_scalar($old[$key])) {
                return $m[0];
            }
            $tag = preg_replace('~\svalue="[^"]*"~i', '', $m[0]);

            return preg_replace_callback('~\s*/?>$~', fn (): string => ' value="'.$escape($old[$key]).'">', $tag);
        }, $html);
        $html = preg_replace_callback('~<select\b[^>]*>.*?</select>~si', function (array $m) use ($old, $escape): string {
            if (! preg_match('~\bname="(plan|interest|topic|city|structure_type|demo_mode)"~', $m[0], $name) || ! is_scalar($old[$name[1]] ?? null)) {
                return $m[0];
            }
            $selected = (string) $old[$name[1]];
            $found = false;
            $select = preg_replace_callback('~<option\b[^>]*>~i', function (array $option) use ($selected, &$found): string {
                $tag = preg_replace('~\sselected(?:="[^"]*")?~i', '', $option[0]);
                if (preg_match('~\bvalue="([^"]*)"~', $tag, $value) && html_entity_decode($value[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') === $selected) {
                    $found = true;

                    return substr($tag, 0, -1).' selected>';
                }

                return $tag;
            }, $m[0]);
            if (! $found && $selected !== '' && $name[1] === 'city') {
                $select = str_replace('</select>', '<option value="'.$escape($selected).'" selected>'.$escape($selected).'</option></select>', $select);
            }

            return $select;
        }, $html);
        if (is_scalar($old['message'] ?? null)) {
            $message = $escape($old['message']);
            $html = preg_replace_callback('~(<textarea\b[^>]*\bname="message"[^>]*>).*?(</textarea>)~si', fn (array $m): string => $m[1].$message.$m[2], $html);
        }

        return str_replace('data-demo-form', 'data-demo-form data-contact-restored="true"', $html);
    }

    public function version(): string
    {
        $files = array_map(fn (string $page): string => 'web/'.$page.'.html', array_keys(self::PAGES));
        array_push($files, 'web/assets/app.js', 'web/assets/styles.css', 'web/assets/catalogo-commerciale.json');
        $context = hash_init('sha256');
        foreach ($files as $file) {
            hash_update($context, $file.':'.hash_file('sha256', base_path($file))."\n");
        }

        return substr(hash_final($context), 0, 24);
    }
}
