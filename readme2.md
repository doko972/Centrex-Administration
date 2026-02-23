# FreePBX Custom Theme – Auto-Apply après mise à jour

## Objectif

Réappliquer automatiquement le thème personnalisé (logos, couleurs, vues PHP) après chaque mise à jour du module **framework** de FreePBX, qui écrase les fichiers custom avec `cp -rf`.

## Prérequis

- FreePBX 16.x sur Debian (systemd 247+)
- Accès root au serveur
- Le script `apply_custom_variables.sh` prêt à déployer
- Le dossier `customtheme` avec tous les assets personnalisés

---

## Architecture

```
/usr/local/bin/apply_custom_variables.sh    ← Script de patch (exécutable)
/var/www/html/admin/modules/customtheme/    ← Fichiers sources custom
├── assets/
│   ├── css/custom.css
│   ├── images/                             ← Logos personnalisés
│   │   ├── favicon.ico
│   │   ├── freepbx_small.png
│   │   ├── support.png
│   │   ├── sys-admin.png
│   │   ├── tango.png
│   │   ├── user-control.png
│   │   └── operator-panel.png
│   ├── less/                               ← Fichiers LESS personnalisés
│   │   ├── variables.less
│   │   ├── freepbx.less
│   │   ├── login.less
│   │   ├── menu.less
│   │   └── ...
│   └── views/                              ← Vues PHP personnalisées
│       ├── footer.php
│       ├── footer_content.php
│       ├── header.php
│       ├── login.php
│       └── menu.php
/etc/systemd/system/customtheme.path        ← Watcher systemd
/etc/systemd/system/customtheme.service     ← Service déclenché
/var/backups/freepbx-theme/                 ← Backups automatiques
```

---

## Étape 1 – Déployer les fichiers custom

### 1.1 Copier le dossier customtheme

Depuis le serveur source, transférer le dossier vers le nouveau serveur :

```bash
scp -r /var/www/html/admin/modules/customtheme/ root@NOUVEAU_SERVEUR:/var/www/html/admin/modules/
```

Vérifier sur le nouveau serveur :

```bash
find /var/www/html/admin/modules/customtheme/ -type f | sort
```

Appliquer les permissions :

```bash
chown -R asterisk:asterisk /var/www/html/admin/modules/customtheme/
```

### 1.2 Copier le script

```bash
scp /usr/local/bin/apply_custom_variables.sh root@NOUVEAU_SERVEUR:/usr/local/bin/
```

Rendre exécutable et vérifier :

```bash
chmod +x /usr/local/bin/apply_custom_variables.sh
ls -la /usr/local/bin/apply_custom_variables.sh
```

### 1.3 Test manuel

Exécuter le script une première fois pour vérifier qu'il fonctionne :

```bash
/usr/local/bin/apply_custom_variables.sh -y
```

Résultat attendu : `🎨 Thème appliqué avec succès !`

---

## Étape 1b – Configurer Apache pour autoriser l'iframe (Dashboard Centrex)

Cette configuration est nécessaire pour que le Dashboard Centrex Admin puisse charger FreePBX dans une iframe via le reverse proxy Nginx. Sans elle, Apache envoie le header `X-Frame-Options: SAMEORIGIN` qui bloque l'affichage.

### 1b.1 Créer le fichier de configuration Apache

```bash
nano /etc/apache2/conf-available/freepbx-iframe.conf
```

Contenu :

```apache
<IfModule mod_headers.c>
    Header unset X-Frame-Options
    Header unset Content-Security-Policy
</IfModule>
```

### 1b.2 Activer la configuration

```bash
a2enmod headers
a2enconf freepbx-iframe
```

### 1b.3 Tester et redémarrer Apache

```bash
apache2ctl configtest
systemctl restart apache2
```

Résultat attendu : `Syntax OK` (l'avertissement `AH00558` sur le ServerName est normal et non bloquant).

### 1b.4 Vérifier

```bash
systemctl status apache2
```

Résultat attendu : `active (running)`

---

## Étape 2 – Créer le watcher systemd

### 2.1 Créer le path unit

Ce fichier surveille `/var/www/html/admin/assets/less/freepbx/variables.less`. Dès qu'il est modifié par une mise à jour FreePBX, le service se déclenche.

```bash
cat > /etc/systemd/system/customtheme.path << 'EOF'
[Unit]
Description=Surveille les fichiers FreePBX pour réappliquer le thème custom

[Path]
PathChanged=/var/www/html/admin/assets/less/freepbx/variables.less

[Install]
WantedBy=multi-user.target
EOF
```

### 2.2 Créer le service

Ce service exécute le script avec un délai de 10 secondes pour laisser FreePBX finir sa mise à jour et la recompilation CSS/LESS.

```bash
cat > /etc/systemd/system/customtheme.service << 'EOF'
[Unit]
Description=Réapplique le thème custom FreePBX après mise à jour
After=network.target

[Service]
Type=oneshot
ExecStartPre=/bin/sleep 10
ExecStart=/usr/local/bin/apply_custom_variables.sh -y
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF
```

---

## Étape 3 – Activer et démarrer

```bash
# Recharger systemd
systemctl daemon-reload

# Activer le watcher au démarrage
systemctl enable customtheme.path

# Démarrer le watcher
systemctl start customtheme.path

# Vérifier le statut
systemctl status customtheme.path
```

Résultat attendu : **`Active: active (waiting)`**

---

## Étape 4 – Tester le déclenchement automatique

Simuler une modification du fichier surveillé (comme le ferait une mise à jour) :

```bash
# Simuler une mise à jour
touch /var/www/html/admin/assets/less/freepbx/variables.less

# Attendre 15 secondes (sleep 10 + exécution du script)
sleep 15

# Vérifier l'exécution
systemctl status customtheme.service
```

Résultat attendu :
- `Process: ... ExecStart=... (code=exited, status=0/SUCCESS)`
- `🎨 Thème appliqué avec succès !` dans les logs

---

## Commandes utiles

| Action | Commande |
|--------|----------|
| État du watcher | `systemctl status customtheme.path` |
| Dernière exécution | `systemctl status customtheme.service` |
| Logs détaillés | `journalctl -u customtheme.service --no-pager -n 30` |
| Lancer manuellement | `/usr/local/bin/apply_custom_variables.sh -y` |
| Stopper le watcher | `systemctl stop customtheme.path` |
| Désactiver au démarrage | `systemctl disable customtheme.path` |

---

## Pourquoi les mises à jour écrasent le thème

Le fichier `install.php` du module **framework** exécute à chaque mise à jour :

```php
$htdocs_source = dirname(__FILE__) . "/amp_conf/htdocs/.";
$htdocs_dest = $amp_conf['AMPWEBROOT']; // = /var/www/html

exec("cp -rf $htdocs_source $htdocs_dest");
```

Puis recompile le CSS/LESS :

```php
compress::web_files();
```

Il n'y a pas d'archive persistante : les fichiers sont téléchargés depuis le dépôt FreePBX, copiés par-dessus les fichiers existants, puis le dossier source est supprimé.

---

## Dépannage

### Le path unit est `inactive (dead)` au lieu de `active (waiting)`

Le fichier surveillé n'existe pas. Vérifier :

```bash
ls -la /var/www/html/admin/assets/less/freepbx/variables.less
```

Si absent, appliquer le thème manuellement d'abord :

```bash
/usr/local/bin/apply_custom_variables.sh -y
systemctl restart customtheme.path
```

### Le service échoue

Vérifier les logs :

```bash
journalctl -u customtheme.service --no-pager -n 50
```

Causes possibles :
- Script non exécutable → `chmod +x /usr/local/bin/apply_custom_variables.sh`
- Fichiers custom manquants → vérifier `/var/www/html/admin/modules/customtheme/`
- Permissions → `chown -R asterisk:asterisk /var/www/html/admin/modules/customtheme/`

### Le thème n'est pas appliqué après une mise à jour

Vérifier que le watcher est actif :

```bash
systemctl status customtheme.path
```

S'il est `inactive`, le redémarrer :

```bash
systemctl start customtheme.path
```

---

## Déploiement rapide (copier-coller)

Script complet pour déployer sur un nouveau serveur (après avoir copié `customtheme/` et le script) :

```bash
#!/bin/bash
# Déploiement rapide du thème custom FreePBX

# Permissions
chown -R asterisk:asterisk /var/www/html/admin/modules/customtheme/
chmod +x /usr/local/bin/apply_custom_variables.sh

# Configuration Apache pour autoriser l'iframe (Dashboard Centrex)
cat > /etc/apache2/conf-available/freepbx-iframe.conf << 'APACHEEOF'
<IfModule mod_headers.c>
    Header unset X-Frame-Options
    Header unset Content-Security-Policy
</IfModule>
APACHEEOF
a2enmod headers
a2enconf freepbx-iframe
apache2ctl configtest && systemctl restart apache2

# Application initiale du thème
/usr/local/bin/apply_custom_variables.sh -y

# Création du path unit
cat > /etc/systemd/system/customtheme.path << 'PATHEOF'
[Unit]
Description=Surveille les fichiers FreePBX pour réappliquer le thème custom

[Path]
PathChanged=/var/www/html/admin/assets/less/freepbx/variables.less

[Install]
WantedBy=multi-user.target
PATHEOF

# Création du service
cat > /etc/systemd/system/customtheme.service << 'SVCEOF'
[Unit]
Description=Réapplique le thème custom FreePBX après mise à jour
After=network.target

[Service]
Type=oneshot
ExecStartPre=/bin/sleep 10
ExecStart=/usr/local/bin/apply_custom_variables.sh -y
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
SVCEOF

# Activation
systemctl daemon-reload
systemctl enable customtheme.path
systemctl start customtheme.path

# Vérification
echo "--- Statut du watcher ---"
systemctl status customtheme.path
```


## Super Utilisateur  ?? Mauvaise stratégie ??
```bash
php artisan tinker
User::create(['name'=>'Super Client', 'email'=>'super@gmail.com', 'password'=>bcrypt('password'), 'role'=>'superclient'])
```