# Centrex Admin Dashboard - Documentation Complète

## 📋 Table des matières
- [Vue d'ensemble](#vue-densemble)
- [Architecture du système](#architecture-du-système)
- [Stack technique](#stack-technique)
- [Installation & Configuration](#installation--configuration)
- [Structure de la base de données](#structure-de-la-base-de-données)
- [Fonctionnalités implémentées](#fonctionnalités-implémentées)
- [Configuration Nginx Reverse Proxy](#configuration-nginx-reverse-proxy)
- [Configuration Apache FreePBX](#configuration-apache-freepbx)
- [Monitoring automatique](#monitoring-automatique)
- [Sécurité](#sécurité)
- [Commandes utiles](#commandes-utiles)
- [Dépannage](#dépannage)

---

## 🎯 Vue d'ensemble

Application Laravel permettant de gérer de manière centralisée plusieurs centrex FreePBX hébergés sur des VM OVHCloud. Le système offre un accès sécurisé via reverse proxy, éliminant le besoin de whitelister les IPs individuelles des clients.

### Objectif principal
**Centraliser l'accès aux centrex** : Seule l'IP du serveur dashboard est whitelistée sur les FreePBX, tous les clients passent par ce point d'accès unique.

---

## 🏗 Architecture du système
```
┌─────────────────────────────────────────────┐
│  Client (n'importe quelle IP)               │
└─────────────────┬───────────────────────────┘
                  │ HTTPS
                  ▼
┌─────────────────────────────────────────────┐
│  Dashboard Laravel (54.38.1.103)            │
│  - Authentification (Admin/Client)          │
│  - Gestion CRUD (Clients & Centrex)         │
│  - Statistiques & Monitoring                │
└─────────────────┬───────────────────────────┘
                  │ Port 8080
                  ▼
┌─────────────────────────────────────────────┐
│  Nginx Reverse Proxy                        │
│  - Proxification des requêtes               │
│  - Authentification automatique Basic Auth  │
│  - Suppression headers X-Frame-Options      │
└─────────────────┬───────────────────────────┘
                  │ HTTP + Auth
                  ▼
┌─────────────────────────────────────────────┐
│  FreePBX Centrex (IPs variées)              │
│  - 51.91.145.39                             │
│  - 54.38.1.185                              │
│  - etc...                                   │
└─────────────────────────────────────────────┘
```

---

## 💻 Stack technique

### Backend
- **Laravel 11** (PHP 8.2.30)
- **MySQL** (Base de données)
- **Guzzle HTTP** (Client HTTP pour proxy)
- **Composer 2.8.8** (Gestionnaire de dépendances PHP)

### Frontend
- **Blade** (Moteur de templates Laravel)
- **Sass** (Préprocesseur CSS)
- **Chart.js** (Graphiques statistiques)
- **JavaScript Vanilla** (Pas de framework)
- **Vite** (Build tool)

### Serveur
- **Debian 13** (Système d'exploitation)
- **Nginx** (Reverse proxy + Serveur web Laravel)
- **Apache** (Sur serveurs FreePBX)
- **PHP-FPM 8.2** (Traitement PHP)

---

## 🚀 Installation & Configuration

### 1. Prérequis
```bash
# Sur serveur Debian 13
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
    php8.2-curl php8.2-zip nginx mysql-server composer npm git
```

### 2. Cloner le projet
```bash
cd /var/www
git clone <votre-repo> centrex-dashboard
cd centrex-dashboard
chown -R www-data:www-data /var/www/centrex-dashboard
chmod -R 755 /var/www/centrex-dashboard
```

### 3. Installation des dépendances
```bash
# PHP
composer install --no-dev --optimize-autoloader

# JavaScript
npm install
npm run build
```

### 4. Configuration
```bash
# Copier le fichier .env
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Configurer .env
nano .env
```

**Configuration `.env` :**
```env
APP_NAME="Centrex Admin Dashboard"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://54.38.1.103

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=centrex_dashboard
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### 5. Base de données
```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE centrex_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Exécuter les migrations
php artisan migrate

# Créer l'utilisateur admin
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=ConnectionTypeSeeder
php artisan db:seed --class=EquipmentSeeder
```

**Identifiants admin par défaut :**
- Email : `admin@centrex.com`
- Password : `password`

### 6. Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
php artisan storage:link
```

---

## 🗄 Structure de la base de données

### Tables principales

#### `users`
```sql
- id
- name
- email
- password
- role (enum: 'admin', 'client')
- created_at
- updated_at
```

#### `clients`
```sql
- id
- user_id (FK -> users.id)
- company_name
- contact_name
- email
- phone
- address
- is_active
- created_at
- updated_at
```

#### `centrex`
```sql
- id
- name
- ip_address
- port (default: 80)
- login
- password (chiffré)
- image
- status (enum: 'online', 'offline', 'maintenance')
- last_check
- description
- is_active
- created_at
- updated_at
```

#### `client_centrex` (pivot)
```sql
- id
- client_id (FK -> clients.id)
- centrex_id (FK -> centrex.id)
- created_at
- updated_at
- UNIQUE(client_id, centrex_id)
```

### Relations
- **User** → hasOne → **Client**
- **Client** → belongsToMany → **Centrex**
- **Centrex** → belongsToMany → **Client**

---

## ⚙️ Fonctionnalités implémentées

### 🔐 Authentification & Sécurité

#### Middlewares
- **IsAdmin** : Vérifie que l'utilisateur est administrateur
- **IsClient** : Vérifie que l'utilisateur est client
- **Auth** : Vérifie l'authentification

#### Redirection intelligente
```php
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin() 
            ? redirect()->route('admin.dashboard')
            : redirect()->route('client.dashboard');
    }
    return redirect()->route('login');
});
```

#### Pages d'erreur personnalisées
- **403** : Accès refusé (avec message contextualisé)
- **404** : Page introuvable (avec lien retour)

---

### 👨‍💼 Partie Administrateur

#### Dashboard admin
- **Compteurs** : Total clients, centrex, en ligne, disponibilité %
- **Graphique** : Statut des centrex (Chart.js - Doughnut)
- **Dernières vérifications** : 5 derniers checks avec statut
- **Actions rapides** : Liens vers gestion clients/centrex

#### CRUD Clients
```php
Route::resource('clients', ClientController::class);
```

**Fonctionnalités :**
- ✅ Liste des clients avec filtres
- ✅ Création client + compte utilisateur automatique
- ✅ Modification (sauf email)
- ✅ Suppression (cascade sur user)
- ✅ Vue détaillée avec centrex associés
- ✅ Gestion des associations Client ↔ Centrex

#### CRUD Centrex
```php
Route::resource('centrex', CentrexController::class);
```

**Fonctionnalités :**
- ✅ Liste des centrex en grille avec images
- ✅ Création avec upload d'image (stored in `storage/app/public/centrex`)
- ✅ Modification avec conservation image si non changée
- ✅ Suppression avec suppression image associée
- ✅ Vue détaillée avec clients associés
- ✅ Gestion statut (online/offline/maintenance)

#### Association Client ↔ Centrex
```php
Route::get('/clients/{client}/manage-centrex', [ClientCentrexController::class, 'manage']);
Route::post('/clients/{client}/manage-centrex', [ClientCentrexController::class, 'update']);
```

**Interface :**
- Checkboxes pour sélection multiple
- Affichage visuel (images, statuts)
- Synchronisation via Eloquent `sync()`

---

### 👤 Partie Client

#### Dashboard client
- Affichage des centrex assignés
- Carte par centrex avec :
  - Image/logo
  - Nom & description
  - Statut (en ligne/hors ligne/maintenance)
  - IP & port
  - Bouton d'accès

#### Accès aux centrex

**2 méthodes disponibles :**

##### 1. Accès direct (avec credentials masqués)
```
Route: /client/centrex/{id}/access
```
- Affiche les identifiants masqués (blur + non-sélectionnables)
- Boutons "Copier" pour login et password
- Ouverture automatique de FreePBX dans nouvelle fenêtre
- Sécurisé : credentials jamais exposés en clair

##### 2. Reverse Proxy Nginx (recommandé)
```
Route: /client/centrex/{id}/nginx-proxy
```
- Chargement de FreePBX dans iframe
- Authentification automatique via Basic Auth
- Aucun identifiant visible pour le client
- **C'EST LA SOLUTION UTILISÉE EN PRODUCTION**

---

## 🔄 Configuration Nginx Reverse Proxy

### Fichier : `/etc/nginx/sites-available/centrex-proxy`
```nginx
server {
    listen 8080;
    server_name _;

    # Logs
    access_log /var/log/nginx/centrex-proxy-access.log;
    error_log /var/log/nginx/centrex-proxy-error.log;

    # Route pour proxifier vers les centrex
    location ~ ^/proxy/([0-9\.]+)/(.*)$ {
        set $centrex_ip $1;
        set $path $2;

        # Proxy vers le FreePBX
        proxy_pass http://$centrex_ip/$path$is_args$args;
        
        # Headers
        proxy_set_header Host $centrex_ip;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # Authentification Basic Auth (encodé en base64)
        # Format: echo -n "login:password" | base64
        proxy_set_header Authorization "Basic <base64_encoded_credentials>";
        
        # Supprimer les headers de sécurité
        proxy_hide_header X-Frame-Options;
        proxy_hide_header Content-Security-Policy;
        
        # Désactiver buffering
        proxy_buffering off;
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
}
```

### Activation
```bash
# Créer le lien symbolique
sudo ln -s /etc/nginx/sites-available/centrex-proxy /etc/nginx/sites-enabled/

# Tester la configuration
sudo nginx -t

# Recharger Nginx
sudo systemctl reload nginx

# Ouvrir le port 8080
sudo ufw allow 8080/tcp
```

### URL d'accès
```
http://54.38.1.103:8080/proxy/<IP_CENTREX>/admin
```

**Exemple :**
```
http://54.38.1.103:8080/proxy/51.91.145.39/admin
```

---

## 🔧 Configuration Apache FreePBX

### Problème : X-Frame-Options
Par défaut, Apache envoie le header `X-Frame-Options: SAMEORIGIN` qui empêche le chargement dans une iframe depuis un autre domaine.

### Solution : Désactiver X-Frame-Options

**Sur chaque serveur FreePBX, créer :**
```bash
nano /etc/apache2/conf-available/freepbx-iframe.conf
```

**Contenu :**
```apache
<IfModule mod_headers.c>
    Header unset X-Frame-Options
    Header unset Content-Security-Policy
</IfModule>
```

**Activer et redémarrer :**
```bash
a2enmod headers
a2enconf freepbx-iframe
apache2ctl configtest
systemctl restart apache2
```

### Whitelisting Fail2Ban

**Sur chaque serveur FreePBX, éditer :**
```bash
nano /etc/fail2ban/jail.local
```

**Ajouter l'IP du dashboard :**
```ini
[DEFAULT]
ignoreip = 127.0.0.1/8 ::1 54.38.1.103
```

**Redémarrer Fail2Ban :**
```bash
systemctl restart fail2ban
```

---

## 📊 Monitoring automatique

### Commande artisan
```bash
php artisan centrex:check-status
```

**Fonctionnement :**
- Récupère tous les centrex actifs
- Effectue une requête HTTP GET vers chaque centrex
- Met à jour le statut (online/offline)
- Enregistre `last_check` (timestamp)
- Affiche un résumé dans la console

### Scheduler Laravel

**Fichier : `routes/console.php`**
```php
Schedule::command('centrex:check-status')->everyFiveMinutes();
```

### Configuration Cron (production)

**Éditer le crontab :**
```bash
crontab -e
```

**Ajouter :**
```bash
* * * * * cd /var/www/centrex-dashboard && php artisan schedule:run >> /dev/null 2>&1
```

### Test manuel du scheduler
```bash
php artisan schedule:work
```

---

## 🎨 Design & Interface

### Architecture Sass
```
resources/sass/
├── app.scss                    # Fichier principal
├── base/
│   ├── _variables.scss        # Couleurs light/dark
│   ├── _reset.scss            # Reset CSS
│   └── _typography.scss       # Typographie
├── layouts/
│   └── _main.scss             # Layout principal
└── components/
    └── _buttons.scss          # Boutons
```

### Thème Light/Dark

**Variables CSS dynamiques :**
```scss
:root {
  --color-primary: #3b82f6;
  --bg-primary: #ffffff;
  --text-primary: #111827;
}

[data-theme="dark"] {
  --color-primary: #60a5fa;
  --bg-primary: #1f2937;
  --text-primary: #f9fafb;
}
```

**Switch thème (JavaScript) :**
```javascript
// Sauvegarde dans localStorage
localStorage.setItem('theme', newTheme);
document.documentElement.setAttribute('data-theme', newTheme);
```

### Compilation
```bash
# Développement (watch mode)
npm run dev

# Production (minifié)
npm run build
```

---

## 🔐 Sécurité

### Chiffrement des mots de passe

**Model Centrex :**
```php
// Chiffrer à la sauvegarde
public function setPasswordAttribute($value)
{
    $this->attributes['password'] = Crypt::encryptString($value);
}

// Déchiffrer à la lecture
public function getPasswordAttribute($value)
{
    return Crypt::decryptString($value);
}
```

### Validation des accès
```php
// Vérifier que le client a accès au centrex
if (!$client->centrex->contains($centrex->id)) {
    abort(403, 'Vous n\'avez pas accès à ce centrex.');
}
```

### Protection CSRF
```blade
<form method="POST" action="{{ route('login') }}">
    @csrf
    <!-- ... -->
</form>
```

### Authentification Basic Auth (Nginx)
```bash
# Générer l'encodage base64
echo -n "login:password" | base64
```

---

## 📝 Commandes utiles

### Laravel
```bash
# Lancer le serveur de développement
php artisan serve

# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Vérifier le statut des centrex
php artisan centrex:check-status

# Lancer le scheduler
php artisan schedule:work

# Créer un utilisateur admin
php artisan db:seed --class=AdminUserSeeder

# Créer un lien symbolique storage
php artisan storage:link
```

### Nginx
```bash
# Tester la configuration
sudo nginx -t

# Recharger la configuration
sudo systemctl reload nginx

# Redémarrer Nginx
sudo systemctl restart nginx

# Voir les logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/centrex-proxy-access.log
```

### Base de données
```bash
# Exporter la BDD
mysqldump -u root -p centrex_dashboard > backup.sql

# Importer la BDD
mysql -u root -p centrex_dashboard < backup.sql

# Se connecter à MySQL
mysql -u root -p
```

### Permissions
```bash
# Réparer les permissions
sudo chown -R www-data:www-data /var/www/centrex-dashboard
sudo chmod -R 755 /var/www/centrex-dashboard
sudo chmod -R 775 /var/www/centrex-dashboard/storage
sudo chmod -R 775 /var/www/centrex-dashboard/bootstrap/cache
```

---

## 🐛 Dépannage

### Problème : Page blanche
```bash
# Activer le mode debug temporairement
nano .env
# APP_DEBUG=true

# Vérifier les logs Laravel
tail -f storage/logs/laravel.log
```

### Problème : Erreur 500
```bash
# Vérifier les permissions
ls -la storage/
ls -la bootstrap/cache/

# Réparer
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Problème : Assets non chargés
```bash
# Recompiler les assets
npm run build

# Vérifier le lien symbolique storage
ls -la public/storage
php artisan storage:link
```

### Problème : Centrex ne se charge pas dans iframe
```bash
# Vérifier que le port 8080 est ouvert
sudo ss -tlnp | grep 8080

# Vérifier les logs Nginx
sudo tail -f /var/log/nginx/centrex-proxy-error.log

# Vérifier que Apache FreePBX autorise l'iframe
curl -I http://51.91.145.39/admin | grep X-Frame-Options
```

### Problème : Monitoring ne fonctionne pas
```bash
# Tester manuellement
php artisan centrex:check-status

# Vérifier le cron
crontab -l

# Voir les logs du scheduler
tail -f storage/logs/laravel.log | grep "centrex:check-status"
```

---

## 📞 Support & Contact

Pour toute question ou problème :
1. Vérifier la section [Dépannage](#dépannage)
2. Consulter les logs : `storage/logs/laravel.log`
3. Vérifier les logs Nginx : `/var/log/nginx/`

---

## 📄 License

Propriétaire - Tous droits réservés

---

## 🎉 Remerciements

Ce projet a été développé avec l'assistance de **Claude (Anthropic)** pour la conception, l'architecture et la résolution des problèmes techniques.

**Date de création :** Janvier 2026  
**Version :** 1.0.0  
**Statut :** ✅ En production

---