<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sessione scaduta | Schedine di Notifica</title>
    <style>body{margin:0;background:#f8fafb;color:#173647;font:18px/1.6 system-ui,sans-serif}main{max-width:680px;margin:12vh auto;padding:32px}h1{line-height:1.15}a{color:#006d75}nav{display:flex;gap:24px;flex-wrap:wrap}a:focus-visible{outline:3px solid #007d85;outline-offset:5px}</style>
</head>
<body>
<main>
    <p>Schedine di Notifica · Sessione scaduta</p>
    <h1>La sessione del modulo è scaduta.</h1>
    <p>La richiesta non è stata registrata. Apri nuovamente la pagina dei contatti e riprova.</p>
    <nav aria-label="Destinazioni utili">
        <a href="{{ route('site.home') }}">Torna alla Home</a>
        <a href="{{ route('site.contatti') }}">Contatti e demo</a>
        <a href="{{ route('login') }}">Accedi al gestionale</a>
    </nav>
</main>
</body>
</html>
