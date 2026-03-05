<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre code de connexion</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 28px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; }
        .body { padding: 36px 40px; text-align: center; }
        .body p { line-height: 1.7; margin: 0 0 16px; color: #334155; font-size: 15px; text-align: left; }
        .code-block { margin: 14px auto; }
        .code-digits { display: inline-block; letter-spacing: 12px; font-size: 32px; font-weight: 700; font-family: monospace; color: #1e293b; background: #f1f5f9; border: 2px solid #2563eb; border-radius: 12px; padding: 9px 14px 9px 20px; }
        .expiry { font-size: 13px; color: #64748b; margin: 16px 0 0; }
        .warning { background: #fefce8; border: 1px solid #fde047; border-radius: 6px; padding: 14px 18px; margin: 24px 0; text-align: left; }
        .warning p { color: #854d0e; font-size: 13px; margin: 0; }
        .footer { background: #f8fafc; padding: 20px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #94a3b8; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>HR Télécoms — Vérification de connexion</h1>
        </div>

        <div class="body">
            <p>Bonjour <strong>{{ $user->name }}</strong>,</p>
            <p>Une connexion à votre compte a été initiée. Voici votre code de vérification :</p>

            <div class="code-block">
                <div class="code-digits">{{ $code }}</div>
                <p class="expiry">Ce code expire dans <strong>10 minutes</strong>.</p>
            </div>

            <div class="warning">
                <p><strong>Ce n'est pas vous ?</strong> Ignorez cet email et changez immédiatement votre mot de passe. Ne communiquez jamais ce code à une tierce personne.</p>
            </div>

            <p style="font-size: 13px; color: #94a3b8; text-align: left;">Cet email a été envoyé suite à une tentative de connexion depuis l'adresse IP {{ request()->ip() }}.</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} HR Télécoms — Ce message est confidentiel.</p>
        </div>
    </div>
</body>
</html>
