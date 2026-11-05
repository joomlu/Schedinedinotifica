<?php

namespace Tests\Feature;

use App\Mail\Transport\WebCheckinSmtpCurl;
use App\Mail\WebCheckinInvito;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class WebCheckinSmtpCurlTest extends TestCase
{
    private function smtp(string $mode, bool $trust, callable $check): void
    {
        $root = storage_path('app/smtp-'.bin2hex(random_bytes(4)));
        mkdir($root, 0700, true);
        $cert = new Process(['openssl', 'req', '-x509', '-newkey', 'rsa:2048', '-nodes', '-subj', '/CN=127.0.0.1',
            '-addext', 'subjectAltName=IP:127.0.0.1', '-days', '1', '-keyout', $root.'/key.pem', '-out', $root.'/cert.pem']);
        $cert->mustRun();
        $server = new Process(['python3', base_path('tests/Isolation/smtp-tls-fixture.py'), $root, $mode]);
        $server->start();
        try {
            $this->assertTrue($server->waitUntil(fn ($type, $output) => str_contains($output, 'porta')));
            $port = json_decode(trim($server->getOutput()), true, 512, JSON_THROW_ON_ERROR)['porta'];
            $config = ['transport' => 'web_checkin_smtps', 'scheme' => 'smtps', 'host' => '127.0.0.1', 'port' => $port,
                'username' => 'utente-sintetico', 'password' => 'password-sintetica', 'timeout' => 5,
                'from' => ['address' => 'reception@example.invalid', 'name' => 'Reception sintetica']];
            if ($trust) $config['cafile'] = $root.'/cert.pem';
            config(['mail.mailers.smtp_sintetico' => $config]);
            Mail::purge('smtp_sintetico');
            file_put_contents($root.'/hotel.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a23cAAAAASUVORK5CYII='));
            $invito = ['oggetto' => 'Invito HTML sintetico',
                'layout' => ['saluto' => 'Gentile Ospite,', 'testo' => 'Completa il check-in online.', 'pulsante' => 'Vai al Check-in', 'firma' => 'Ti aspettiamo!'],
                'identita' => ['nome' => 'Hotel TLS sintetico', 'colore' => '#345678', 'testo_colore' => '#ffffff',
                    'logo_path' => $root.'/hotel.png', 'logo_url' => null, 'logo_width' => 1, 'logo_height' => 1,
                    'email' => 'reception@example.invalid', 'telefono' => '0000000000'],
                'dati' => ['link' => 'https://hotel.example.invalid/checkin/sintetico', 'persone' => 1, 'prenotazione' => 'SMTP-SINTETICA',
                    'arrivo' => '10/11/2026', 'partenza' => '13/11/2026', 'scadenza' => '12/11/2026 00:00:00 CET']];
            $check($root, $invito, $config);
        } finally {
            $server->stop(2);
        }
    }

    public function test_smtps_reale_isolato_con_tls_autenticazione_html_testo_e_logo_cid(): void
    {
        $this->smtp('accetta', true, function ($root, $invito, $config) {
            $sent = Mail::mailer('smtp_sintetico')->to('controllato@example.invalid')->send(new WebCheckinInvito($invito));
            $this->assertNotNull($sent);
            $proof = json_decode(file_get_contents($root.'/ricevuta.json'), true, 512, JSON_THROW_ON_ERROR);
            foreach (['autenticato', 'HTML', 'testo', 'logo_CID'] as $key) $this->assertTrue($proof[$key], $key);
            $this->assertSame('accetta', $proof['modo']);
            $mime = file_get_contents($root.'/ricevuto.eml');
            $this->assertStringContainsString('SMTP-SINTETICA', $mime);
            $this->assertStringContainsString('hotel.example.invalid/checkin/sintetico', quoted_printable_decode($mime));
            $this->assertStringNotContainsString('password-sintetica', $mime);
            $this->assertSame('web-checkin-smtps', (string) new WebCheckinSmtpCurl($config));
        });
    }

    public function test_certificato_non_fidato_impedisce_dati_e_accettazione(): void
    {
        $this->smtp('accetta', false, function ($root, $invito) {
            try {
                Mail::mailer('smtp_sintetico')->to('controllato@example.invalid')->send(new WebCheckinInvito($invito));
                $this->fail('Certificato non fidato accettato');
            } catch (TransportException $e) {
                $this->assertInstanceOf(\App\Mail\Transport\WebCheckinSmtpRifiutato::class, $e);
                $this->assertSame('connessione', $e->motivo);
                $this->assertFileDoesNotExist($root.'/ricevuto.eml');
            }
        });
    }

    public function test_rifiuto_smtp_e_caduta_prima_del_250_non_dichiarano_invio(): void
    {
        foreach (['rifiuta', 'incerto'] as $mode) {
            $this->smtp($mode, true, function ($root, $invito) use ($mode) {
                try {
                    Mail::mailer('smtp_sintetico')->to('controllato@example.invalid')->send(new WebCheckinInvito($invito));
                    $this->fail('Accettazione attribuita senza risposta 250');
                } catch (TransportException $e) {
                    if ($mode === 'rifiuta') {
                        $this->assertInstanceOf(\App\Mail\Transport\WebCheckinSmtpRifiutato::class, $e);
                        $this->assertSame('rifiuto_smtp', $e->motivo);
                    } else {
                        $this->assertNotInstanceOf(\App\Mail\Transport\WebCheckinSmtpRifiutato::class, $e);
                        $this->assertSame('Accettazione SMTPS non verificata.', $e->getMessage());
                    }
                    $this->assertSame($mode, json_decode(file_get_contents($root.'/ricevuta.json'), true)['modo']);
                }
            });
        }
    }
}
