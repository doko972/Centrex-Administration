<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adresse email modifiée</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 28px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; }
        .body { padding: 36px 40px; text-align: center; }
        .body p { line-height: 1.7; margin: 0 0 16px; color: #334155; font-size: 15px; text-align: left; }
        .change-block { background: #f1f5f9; border-radius: 8px; padding: 14px 18px; margin: 20px 0; text-align: left; font-size: 14px; }
        .warning { background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 14px 18px; margin: 24px 0; text-align: left; }
        .warning p { color: #991b1b; font-size: 13px; margin: 0; }
        .footer { background: #f8fafc; padding: 20px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #94a3b8; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>HR Télécoms — Adresse email modifiée</h1>
        </div>

        <div class="body">
            <p>Bonjour <strong>{{ $user->name }}</strong>,</p>
            <p>L'adresse email associée à votre compte vient d'être modifiée, le {{ now()->format('d/m/Y à H:i') }}.</p>

            <div class="change-block">
                <p style="margin: 0;">Ancienne adresse : <strong>{{ $oldEmail }}</strong></p>
                <p style="margin: 4px 0 0;">Nouvelle adresse : <strong>{{ $newEmail }}</strong></p>
            </div>

            <div class="warning">
                <p><strong>Vous n'êtes pas à l'origine de cette action ?</strong> Contactez immédiatement le support HR Télécoms — votre compte est peut-être compromis.</p>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} HR Télécoms — Ce message est confidentiel.</p>
        </div>
    </div>
</body>
</html>
