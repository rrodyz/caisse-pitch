<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Wave</title>
    <style>
        body { margin:0; font-family: system-ui, sans-serif; background:#08080f; color:#e0e0ee;
               display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
        .card { text-align:center; max-width:340px; }
        .icon { width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                margin:0 auto 18px; font-size:38px; }
        .ok  { background:rgba(16,185,129,.15); color:#34d399; }
        .err { background:rgba(239,68,68,.15); color:#f87171; }
        h1 { font-size:20px; margin:0 0 8px; }
        p { color:#88889a; font-size:14px; line-height:1.5; }
    </style>
</head>
<body>
    <div class="card">
        @if($status === 'success')
            <div class="icon ok">✓</div>
            <h1>Paiement reçu</h1>
            <p>Merci. Votre paiement a bien été pris en compte. Vous pouvez retourner à la caisse.</p>
        @else
            <div class="icon err">✕</div>
            <h1>Paiement non abouti</h1>
            <p>Le paiement a été annulé ou a échoué. Rapprochez-vous de la caisse pour réessayer.</p>
        @endif
    </div>
</body>
</html>
