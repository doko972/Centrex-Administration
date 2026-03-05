<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue — Vos identifiants de connexion</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px; }
        .body { padding: 36px 40px; }
        .body p { line-height: 1.7; margin: 0 0 16px; color: #334155; font-size: 15px; }
        .credentials { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
        .credentials h3 { margin: 0 0 14px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; }
        .credential-row { display: block; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px solid #e2e8f0; }
        .credential-row:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .credential-label { display: block; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
        .credential-value { display: block; font-size: 15px; font-weight: 600; color: #1e293b; font-family: monospace; background: #ffffff; padding: 8px 12px; border-radius: 4px; border: 1px solid #e2e8f0; word-break: break-all; }
        .cta { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 6px; font-weight: 600; font-size: 15px; }
        .warning { background: #fefce8; border: 1px solid #fde047; border-radius: 6px; padding: 14px 18px; margin: 20px 0; }
        .warning p { color: #854d0e; font-size: 13px; margin: 0; }
        .footer { background: #f8fafc; padding: 20px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #94a3b8; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>HR Télécoms</h1>
            <p>Votre espace client est prêt</p>
        </div>

        <div class="body">
            <p>Bonjour <strong>{{ $user->name }}</strong>,</p>

            <p>Votre compte a été créé sur la plateforme de <strong>HR Télécoms</strong>. Vous trouverez ci-dessous vos identifiants de connexion provisoires.</p>

            <div class="credentials">
                <h3>Identifiants de connexion</h3>
                <div class="credential-row">
                    <span class="credential-label">Adresse e-mail</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Mot de passe provisoire</span>
                    <span class="credential-value">{{ $plainPassword }}</span>
                </div>
            </div>

            <div class="warning">
                <p>
                    <strong>Action requise :</strong> lors de votre première connexion, vous serez invité à définir un nouveau mot de passe personnel. Ce mot de passe provisoire ne sera plus valide après ce changement.
                </p>
            </div>

            <div class="cta">
                <a href="{{ url('/login') }}" class="btn">Se connecter</a>
            </div>

            <p style="font-size: 13px; color: #94a3b8;">Si vous n'êtes pas à l'origine de cette demande ou si vous avez des questions, contactez votre administrateur.</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} HR Télécoms — Ce message est confidentiel.</p>
        </div>
    </div>
</body>
</html>
