<!doctype html>
<html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $invito['oggetto'] }}</title></head>
<body style="margin:0;background:#f3f5f4;color:#263238;font-family:Arial,Helvetica,sans-serif;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f5f4"><tr><td align="center" style="padding:28px 12px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:14px"><tr><td style="padding:32px 28px">
@if($invito['identita']['logo_path'])
<p style="margin:0 0 28px;text-align:center"><img src="{{ $logo_src ?? $message->embed($invito['identita']['logo_path']) }}" alt="{{ $invito['identita']['nome'] }}" width="{{ $invito['identita']['logo_width'] }}" height="{{ $invito['identita']['logo_height'] }}" style="max-width:100%;height:auto;object-fit:contain"></p>
@else
<p style="text-align:center;font-size:22px;font-weight:bold">{{ $invito['identita']['nome'] }}</p>
@endif
<p>{{ $invito['layout']['saluto'] }}</p>
<p>{!! nl2br(e($invito['layout']['testo'])) !!}</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 0"><a href="{{ $invito['dati']['link'] }}" style="display:inline-block;background:{{ $invito['identita']['colore'] }};color:{{ $invito['identita']['testo_colore'] }};text-decoration:none;font-weight:bold;font-size:18px;padding:16px 30px;border-radius:7px">{{ $invito['layout']['pulsante'] }}</a></td></tr></table>
<p>{{ (int)$invito['dati']['persone'] === 1 ? 'Inserisci i tuoi dati.' : 'Inserisci i tuoi dati e quelli degli altri ospiti.' }}<br>Puoi salvare e continuare più tardi.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7f7;border:1px solid #e0e7e6;border-radius:8px"><tr><td style="padding:18px 20px">
<strong>Prenotazione:</strong> {{ $invito['dati']['prenotazione'] }}<br><strong>Arrivo:</strong> {{ $invito['dati']['arrivo'] }}<br><strong>Partenza:</strong> {{ $invito['dati']['partenza'] }}<br><strong>Ospiti:</strong> {{ $invito['dati']['persone'] }}
</td></tr></table>
<p style="margin-top:26px">{!! nl2br(e($invito['layout']['firma'])) !!}</p>
@if($invito['identita']['email'])<p style="margin:0">{{ $invito['identita']['email'] }}</p>@endif
@if($invito['identita']['telefono'])<p style="margin:0">{{ $invito['identita']['telefono'] }}</p>@endif
<p style="font-size:12px;color:#526568;margin-top:24px">Link disponibile fino al {{ $invito['dati']['scadenza'] }}. Dopo questo orario contatta la reception.</p>
<p style="font-size:12px;color:#526568">Se il pulsante non funziona, <a href="{{ $invito['dati']['link'] }}" style="color:#245563">apri il modulo da questo collegamento</a>.</p>
</td></tr></table></td></tr></table></body></html>
