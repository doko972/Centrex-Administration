<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 28px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; }
        .body { padding: 36px 40px; text-align: center; }
        .body p { line-height: 1.7; margin: 0 0 16px; color: #334155; font-size: 15px; text-align: left; }
        .btn-block { margin: 24px 0; }
        .btn-reset { display: inline-block; background: #2563eb; color: #ffffff !important; text-decoration: none; font-weight: 700; font-size: 15px; padding: 12px 28px; border-radius: 8px; }
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
            <h1>HR Télécoms — Réinitialisation de mot de passe</h1>
        </div>

        <div class="body">
            <p>Bonjour <strong>{{ $user->name }}</strong>,</p>
            <p>Vous avez demandé la réinitialisation du mot de passe de votre compte. Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>

            <div class="btn-block" style="text-align: center;">
                <a href="{{ $resetUrl }}" class="btn-reset">Réinitialiser mon mot de passe</a>
                <p class="expiry">Ce lien expire dans <strong>60 minutes</strong>.</p>
            </div>

            <div class="warning">
                <p><strong>Vous n'êtes pas à l'origine de cette demande ?</strong> Ignorez simplement cet email, votre mot de passe restera inchangé.</p>
            </div>

            <p style="font-size: 13px; color: #94a3b8; text-align: left;">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>{{ $resetUrl }}</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} HR Télécoms — Ce message est confidentiel.</p>
        </div>
    </div>
</body>
</html>
