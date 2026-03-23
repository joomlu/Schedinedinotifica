<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manuale utente</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; margin: 28px; line-height: 1.45; }
        h1, h2, h3 { margin: 0 0 10px; }
        h1 { font-size: 24px; }
        h2 { font-size: 18px; margin-top: 28px; border-bottom: 1px solid #d1d5db; padding-bottom: 6px; }
        h3 { font-size: 14px; margin-top: 18px; }
        p { margin: 0 0 10px; }
        ul, ol { margin: 8px 0 0 20px; padding: 0; }
        li { margin-bottom: 6px; }
        .muted { color: #6b7280; }
        .section { margin-bottom: 20px; }
        .grid { display: block; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 16px; margin-bottom: 14px; page-break-inside: avoid; }
        .print-actions { margin-bottom: 18px; }
        .print-actions button { padding: 10px 14px; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; cursor: pointer; }
        @media print {
            .print-actions { display: none; }
            body { margin: 14px; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Stampa / Salva in PDF</button>
    </div>

    <h1>Manuale utente</h1>
    <p class="muted">Guida operativa del sistema con percorsi separati tra centro generale e guida amministrativa.</p>

    @if(empty($section) && empty($module))
        <h2>Indice del manuale</h2>
        <div class="card">
            <ol>
                <li>Guida rapida</li>
                <li>Admin e superadmin</li>
                <li>Impostazioni e gestione</li>
                <li>Moduli del sistema</li>
                <li>Centro di supporto</li>
                <li>Domande frequenti</li>
                <li>Problemi comuni e soluzione rapida</li>
            </ol>
        </div>
    @endif

    @if(count($guide))
        <h2>Guida rapida</h2>
        @foreach($guide as $item)
            <div class="card">
                <h3>{{ $item['title'] }}</h3>
                <p class="muted">{{ $item['summary'] }}</p>
                <ol>
                    @foreach($item['steps'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
                <p><strong>Risultato:</strong> {{ $item['result'] }}</p>
            </div>
        @endforeach
    @endif

    @if(count($adminTopics))
        <h2>Admin e superadmin</h2>
        @foreach($adminTopics as $item)
            <div class="card">
                <h3>{{ $item['title'] }}</h3>
                <p class="muted">{{ $item['summary'] }}</p>
                <p><strong>Quando lo usi:</strong> {{ $item['when'] }}</p>
                @if(!empty($item['details']))
                    <p><strong>Cosa significa:</strong></p>
                    <ul>
                        @foreach($item['details'] as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                @endif
                @if(!empty($item['field_groups']))
                    @foreach($item['field_groups'] as $group)
                        <p><strong>{{ $group['title'] }}:</strong></p>
                        <ul>
                            @foreach($group['items'] as $entry)
                                <li>{{ $entry }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                @endif
                <p><strong>Risultato:</strong> {{ $item['result'] }}</p>
            </div>
        @endforeach
    @endif

    @if(count($personas) || count($managementTopics))
        <h2>Impostazioni e gestione</h2>
        @foreach($managementTopics as $item)
            <div class="card">
                <h3>{{ $item['title'] }}</h3>
                <p class="muted">{{ $item['summary'] }}</p>
                <p><strong>Quando lo usi:</strong> {{ $item['when'] }}</p>
                @if(!empty($item['details']))
                    <p><strong>Punti importanti:</strong></p>
                    <ul>
                        @foreach($item['details'] as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                @endif
                @if(!empty($item['field_groups']))
                    @foreach($item['field_groups'] as $group)
                        <p><strong>{{ $group['title'] }}:</strong></p>
                        <ul>
                            @foreach($group['items'] as $entry)
                                <li>{{ $entry }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                @endif
                <p><strong>Risultato:</strong> {{ $item['result'] }}</p>
            </div>
        @endforeach

        @foreach($personas as $item)
            <div class="card">
                <h3>{{ $item['title'] }}</h3>
                <p class="muted">{{ $item['summary'] }}</p>
                <ol>
                    @foreach($item['steps'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
                @if(!empty($item['details']))
                    <p><strong>Punti importanti:</strong></p>
                    <ul>
                        @foreach($item['details'] as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                @endif
                @if(!empty($item['field_groups']))
                    @foreach($item['field_groups'] as $group)
                        <p><strong>{{ $group['title'] }}:</strong></p>
                        <ul>
                            @foreach($group['items'] as $entry)
                                <li>{{ $entry }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                @endif
                <p><strong>Risultato:</strong> {{ $item['result'] }}</p>
            </div>
        @endforeach
    @endif

    @if(count($modules))
        <h2>Moduli del sistema</h2>
        @foreach($modules as $module)
            <div class="card">
                <h3>{{ $module['title'] }}</h3>
                <p class="muted">{{ $module['summary'] }}</p>
                <p><strong>Quando lo usi:</strong> {{ $module['when'] }}</p>
                <ul>
                    @foreach($module['items'] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
                @if(!empty($module['details']))
                    <p><strong>Campi e punti importanti:</strong></p>
                    <ul>
                        @foreach($module['details'] as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                @endif
                @if(!empty($module['field_groups']))
                    @foreach($module['field_groups'] as $group)
                        <p><strong>{{ $group['title'] }}:</strong></p>
                        <ul>
                            @foreach($group['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                @endif
                <p><strong>Risultato:</strong> {{ $module['result'] }}</p>
            </div>
        @endforeach
    @endif

    @if(count($faqs))
        <h2>Domande frequenti</h2>
        @foreach($faqs as $faq)
            <div class="card">
                <h3>{{ $faq['question'] }}</h3>
                <p>{{ $faq['answer'] }}</p>
            </div>
        @endforeach
    @endif

    @if(count($troubleshooting))
        <h2>Problemi comuni e soluzione rapida</h2>
        @foreach($troubleshooting as $item)
            <div class="card">
                <h3>{{ $item['title'] }}</h3>
                <p class="muted">{{ $item['problem'] }}</p>
                <ol>
                    @foreach($item['solution'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </div>
        @endforeach
    @endif
</body>
</html>
