<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Scheda cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --tango: #8c1d2c;
            --tango-dark: #741826;
            --gold: #c6a15b;
            --gold-soft: #efe2c2;
            --ivory: #fcf8f3;
            --paper: #fffdfa;
            --soft: #f8f1e8;
            --border: #cfbda8;
            --text: #2b2b2b;
            --muted: #7b746d;
            --shadow: rgba(43, 26, 29, 0.10);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 18px;
            background:
                radial-gradient(circle at top left, rgba(198, 161, 91, 0.12), transparent 26%),
                linear-gradient(180deg, #f8f2eb 0%, #f3ece4 100%);
            color: var(--text);
            font-family: "Georgia", "Times New Roman", serif;
        }

        .print-actions {
            max-width: 210mm;
            margin: 0 auto 10px;
            display: flex;
            justify-content: flex-end;
        }

        .print-actions button {
            padding: 9px 14px;
            border: 1px solid rgba(140, 29, 44, 0.18);
            background: linear-gradient(180deg, #fffdfa 0%, #f6ede4 100%);
            color: var(--tango-dark);
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .02em;
            box-shadow: 0 8px 20px rgba(43, 26, 29, 0.08);
        }

        .sheet {
            max-width: 210mm;
            margin: 0 auto;
            background: var(--paper);
            padding: 10px;
            border: 1px solid rgba(140, 29, 44, 0.10);
            border-radius: 18px;
            box-shadow: 0 20px 50px var(--shadow);
        }

        .sheet-title {
            border: 1px solid rgba(198, 161, 91, 0.45);
            background:
                linear-gradient(135deg, rgba(140, 29, 44, 0.96), rgba(116, 24, 38, 0.96)),
                linear-gradient(180deg, #8c1d2c 0%, #741826 100%);
            color: #fff8f1;
            text-align: center;
            padding: 14px 16px;
            margin-bottom: 12px;
            border-radius: 14px;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.10);
        }

        .section {
            border: 1px solid var(--border);
            margin-bottom: 12px;
            border-radius: 14px;
            overflow: hidden;
            background: linear-gradient(180deg, #fffdfa 0%, #fdf8f2 100%);
        }

        .section-title {
            padding: 9px 12px;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(198, 161, 91, 0.34);
            background: linear-gradient(180deg, #f9efe3 0%, #f4e7d7 100%);
            color: var(--tango-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border-right: 1px solid rgba(207, 189, 168, 0.85);
            border-bottom: 1px solid rgba(207, 189, 168, 0.85);
            padding: 7px 9px;
            vertical-align: middle;
            font-size: 14px;
        }

        tr:last-child td,
        tr:last-child th {
            border-bottom: 0;
        }

        td:last-child,
        th:last-child {
            border-right: 0;
        }

        .label {
            font-weight: 700;
            margin-right: 4px;
            color: var(--tango-dark);
        }

        .two-col td {
            width: 50%;
        }

        .history-table th {
            background: linear-gradient(180deg, #f9efe3 0%, #f2e3d1 100%);
            font-size: 13px;
            text-align: left;
            color: var(--tango-dark);
        }

        .history-table td {
            font-size: 12px;
        }

        .summary {
            color: var(--muted);
            font-size: 12px;
            padding: 9px 10px 2px;
            font-style: italic;
        }

        .compact-note {
            min-height: 34px;
        }

        .muted {
            color: var(--muted);
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .print-actions {
                display: none;
            }

            .sheet {
                box-shadow: none;
                max-width: 100%;
                padding: 0;
                border: 0;
                border-radius: 0;
            }

            .sheet-title {
                color: #000;
                background: #fff;
                border: 1px solid #777;
                box-shadow: none;
            }

            .section,
            .section-title,
            .history-table th {
                background: #fff !important;
            }

            @page {
                size: A4 portrait;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Stampa / Salva PDF</button>
    </div>

    <div class="sheet">
        <div class="sheet-title">Scheda cliente</div>

        <div class="section">
            <div class="section-title">Cliente residenza</div>
            <table class="two-col">
                <tr>
                    <td><span class="label">Num.:</span>{{ $customer->numero_cliente ?: '—' }}</td>
                    <td><span class="label">Nome:</span>{{ trim(($customer->surname ?? '') . ' ' . ($customer->name ?? '')) ?: '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Nazione:</span>{{ $customer->display_country ?: '—' }}</td>
                    <td><span class="label">Regione:</span>{{ $customer->region ?: '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Provincia:</span>{{ $customer->province ?: '—' }}</td>
                    <td><span class="label">Cap:</span>{{ $customer->cap ?: '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Città:</span>{{ $customer->display_city ?: '—' }}</td>
                    <td><span class="label">Strada:</span>{{ trim(collect([$customer->typeaway, $customer->address, $customer->number])->filter()->implode(' ')) ?: '—' }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="compact-note"><span class="label">Osservazione:</span>{{ $customer->observation ?: '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Anagrafica e documento</div>
            <table class="two-col">
                <tr>
                    <td><span class="label">Data di nascita:</span>{{ !empty($customer->nac_reg) ? \Carbon\Carbon::parse($customer->nac_reg)->format('d/m/Y') : '—' }}</td>
                    <td><span class="label">Doc. tipo / num.:</span>{{ trim(collect([$customer->type_doc_reg, $customer->num_doc_reg])->filter()->implode(' / ')) ?: '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Nazione (di nascita):</span>{{ $customer->display_country_reg ?: '—' }}</td>
                    <td><span class="label">Provincia (di nascita):</span>{{ $customer->prov_reg ?: '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Città (di nascita):</span>{{ $customer->display_city_reg ?: '—' }}</td>
                    <td><span class="label">Cittadinanza:</span>{{ $customer->ciudadania_reg ?: '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Rilasciato il:</span>{{ !empty($customer->date_pub_reg) ? \Carbon\Carbon::parse($customer->date_pub_reg)->format('d/m/Y') : '—' }}</td>
                    <td><span class="label">Scade il:</span>{{ !empty($customer->expire_reg) ? \Carbon\Carbon::parse($customer->expire_reg)->format('d/m/Y') : '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Rilasciato da:</span>{{ $customer->rilasciato_reg ?: '—' }}</td>
                    <td><span class="label">Paese / città rilascio:</span>{{ trim(collect([$customer->display_country_doc_reg, $customer->display_city_doc_reg])->filter()->implode(' / ')) ?: '—' }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="compact-note"><span class="label">Osservazione:</span>{{ $customer->observation_reg ?: '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Contatti e riepilogo</div>
            <table class="two-col">
                <tr>
                    <td><span class="label">Telefono:</span>{{ $customer->phone ?: '—' }}</td>
                    <td><span class="label">Cellulare:</span>{{ $customer->cellphone ?: '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Email:</span>{{ $customer->email ?: '—' }}</td>
                    <td><span class="label">Fax:</span>{{ $customer->fax ?: '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Soggiorni registrati:</span>{{ $storicoSummary['totale_soggiorni'] ?? 0 }}</td>
                    <td><span class="label">Componenti storici:</span>{{ $storicoSummary['totale_componenti'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><span class="label">Privacy / Marketing:</span>{{ $customer->privacy_consent ? 'SI' : 'NO' }} / {{ $customer->marketing_consent ? 'SI' : 'NO' }}</td>
                    <td><span class="label">Comunicazioni:</span>{{ $customer->communication_consent ? 'SI' : 'NO' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Primo arrivo:</span>{{ $storicoSummary['primo_arrivo']?->format('d/m/Y') ?: '—' }}</td>
                    <td><span class="label">Ultimo arrivo:</span>{{ $storicoSummary['ultimo_arrivo']?->format('d/m/Y') ?: '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Storico sintetico</div>
            <div class="summary">Ultimi {{ $storicoSintetico->count() }} soggiorni su {{ $storicoTotale }} registrati</div>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Num</th>
                        <th>Arrivo</th>
                        <th>Partenza</th>
                        <th>Giorni</th>
                        <th>Comp.</th>
                        <th>Registrata</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($storicoSintetico as $riga)
                        <tr>
                            <td>{{ $riga['scheda'] }}</td>
                            <td>{{ $riga['arrivo']?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ $riga['partenza']?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ $riga['giorni'] ?? '—' }}</td>
                            <td>{{ $riga['componenti'] }}</td>
                            <td>{{ $riga['registrata_il'] ? $riga['registrata_il']->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ $riga['osservazione'] ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted">Nessuno storico schedine collegato a questo cliente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
