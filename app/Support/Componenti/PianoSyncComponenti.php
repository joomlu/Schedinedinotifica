<?php

namespace App\Support\Componenti;

final class PianoSyncComponenti
{
    private function __construct(
        public readonly array $aggiornamenti,
        public readonly array $creazioni,
        public readonly array $idDaEliminare,
        public readonly array $errori,
        public readonly array $idMantenuti,
    ) {
    }

    public static function costruisci(array $righe, array $idConsentiti): self
    {
        $aggiornamenti = [];
        $creazioni = [];
        $errori = [];
        $idMantenuti = [];
        $consentiti = array_fill_keys(array_map('intval', $idConsentiti), true);

        foreach ($righe as $index => $row) {
            $id = $row['id'] ?? null;
            if ($id === null || $id === '') {
                $creazioni[] = $row;
                continue;
            }

            $id = (int) $id;

            if (!isset($consentiti[$id])) {
                $errori["componenti.$index.id"] = 'Componente #' . ($index + 1) . ': identificatore non valido per questa schedina.';
                continue;
            }

            if (isset($idMantenuti[$id])) {
                $errori["componenti.$index.id"] = 'Componente #' . ($index + 1) . ': identificatore duplicato nel submit.';
                continue;
            }

            $idMantenuti[$id] = true;
            $aggiornamenti[$id] = $row;
        }

        return new self(
            aggiornamenti: $aggiornamenti,
            creazioni: $creazioni,
            idDaEliminare: array_values(array_diff(array_keys($consentiti), array_keys($idMantenuti))),
            errori: $errori,
            idMantenuti: array_keys($idMantenuti),
        );
    }

    public static function devePreservareEsistentiSuPlaceholder(
        int $conteggioEsistenti,
        array $righeRaw,
        array $righeNormalizzate,
        bool $eliminazioneTotaleIntenzionale = false
    ): bool
    {
        if ($eliminazioneTotaleIntenzionale) {
            return false;
        }

        return $conteggioEsistenti > 0 && !empty($righeRaw) && empty($righeNormalizzate);
    }
}