<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/** SMTPS nativo cURL: conserva il blocco PHP di stream_socket_client del pool. */
final class WebCheckinSmtpCurl extends AbstractTransport
{
    public function __construct(private array $config)
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'web-checkin-smtps';
    }

    protected function doSend(SentMessage $message): void
    {
        $host = (string) ($this->config['host'] ?? '');
        $port = (int) ($this->config['port'] ?? 465);
        $sender = $message->getEnvelope()->getSender()->getAddress();
        $recipients = $message->getEnvelope()->getRecipients();
        if (! function_exists('curl_init') || ! in_array('smtps', curl_version()['protocols'], true)
            || ($this->config['scheme'] ?? '') !== 'smtps' || ! preg_match('/^[a-zA-Z0-9.-]+$/D', $host)
            || $port < 1 || $port > 65535 || count($recipients) !== 1
            || ! filter_var($sender, FILTER_VALIDATE_EMAIL)
            || ! hash_equals((string) ($this->config['from']['address'] ?? ''), $sender)
            || ! filter_var($recipients[0]->getAddress(), FILTER_VALIDATE_EMAIL)
            || empty($this->config['username']) || empty($this->config['password'])) {
            throw new WebCheckinSmtpRifiutato('configurazione');
        }

        $raw = $message->toString();
        $offset = 0;
        $curl = curl_init();
        try {
            $options = [
                CURLOPT_URL => 'smtps://'.$host.':'.$port,
                CURLOPT_PROTOCOLS => CURLPROTO_SMTPS,
                CURLOPT_PROXY => '',
                CURLOPT_NOPROXY => '*',
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_USERNAME => $this->config['username'],
                CURLOPT_PASSWORD => $this->config['password'],
                CURLOPT_MAIL_FROM => $sender,
                CURLOPT_MAIL_RCPT => [$recipients[0]->getAddress()],
                CURLOPT_UPLOAD => true,
                CURLOPT_INFILESIZE => strlen($raw),
                CURLOPT_READFUNCTION => static function ($handle, $stream, int $length) use (&$offset, $raw): string {
                    $part = substr($raw, $offset, $length);
                    $offset += strlen($part);
                    return $part;
                },
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => min(30, max(1, (int) ($this->config['timeout'] ?? 15))),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
            ];
            if (! empty($this->config['cafile'])) $options[CURLOPT_CAINFO] = $this->config['cafile'];
            if (! curl_setopt_array($curl, $options)) throw new WebCheckinSmtpRifiutato('configurazione');
            $result = curl_exec($curl);
            $code = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            if ($result === false || $code !== 250 || $offset !== strlen($raw)) {
                // Rifiuto esplicito oppure nessun byte del messaggio trasferito: ripetibile dopo correzione.
                if ($code >= 400 && $code <= 599) throw new WebCheckinSmtpRifiutato('rifiuto_smtp');
                if ($offset === 0 && $result === false) throw new WebCheckinSmtpRifiutato('connessione');
                // Un timeout dopo DATA può seguire un'accettazione: conservare l'incertezza.
                throw new TransportException('Accettazione SMTPS non verificata.');
            }
        } finally {
            curl_close($curl);
        }
    }
}
