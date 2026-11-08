# Guide de déploiement pas-à-pas — OVH Hosting Pro (PHP 8.4, MySQL, SSH/SFTP)

Domaine : **`joie-enseignante.com`** · Cluster : **`cluster130`** · Login : **`joieeni`**

> **Règle de sécurité appliquée** : aucun mot de passe ni clé secrète n'apparaît dans ce guide, dans les scripts ou dans Git. Toutes les valeurs sensibles sont lues depuis un fichier local non versionné (`.env.deploy`) ou saisies à l'invite (`read -rsp`).

---

## 0. Valeurs OVH à me confirmer avant toute commande distante

Avant d'exécuter quoi que ce soit vers OVH, confirme-moi ces **7 valeurs exactes** depuis ton espace client OVH (**Web Cloud → Hébergements → `joieeni.cluster130.hosting.ovh.net`**) :

| # | Paramètre à confirmer | Où le lire dans l'espace client OVH | Valeur à me confirmer |
|---|---|---|---|
| 1 | **Hôte SSH / SFTP** | Onglet *FTP - SSH* → colonne « Serveur SFTP / SSH » | ex. `ssh.cluster130.hosting.ovh.net` (et port SSH, généralement `22`) |
| 2 | **Identifiant SSH / SFTP** | Onglet *FTP - SSH* → colonne « Login » + état « SSH activé » | `joieeni` (confirmer que la colonne SSH est bien sur **Activé**) |
| 3 | **Dossier racine du domaine** | Onglet *Multisite* → ligne `joie-enseignante.com` → « Dossier racine » | ex. `www` (soit `/home/joieeni/www` en chemin complet) |
| 4 | **Hôte de la base MySQL** | Onglet *Bases de données* → « Adresse du serveur » | ex. `xxxxx.mysql.db` *(ne rien supposer)* |
| 5 | **Nom de la base MySQL** | Onglet *Bases de données* → « Nom de la base » | à confirmer |
| 6 | **Utilisateur de la base MySQL** | Onglet *Bases de données* → « Nom d'utilisateur » | à confirmer |
| 7 | **Adresse e-mail expéditrice créée** | *Web Cloud → E-mails → `joie-enseignante.com`* | ex. `contact@joie-enseignante.com` |

Prépare ensuite sur ton poste (hors Git, déjà ignoré par `.gitignore`) le fichier **`.env.deploy`** :

```bash
cat > .env.deploy <<'EOF'
OVH_SSH_USER="joieeni"
OVH_SSH_HOST="A_CONFIRMER"
OVH_SSH_PORT="22"
OVH_REMOTE_DIR="/home/joieeni/www"

DB_HOST="A_CONFIRMER.mysql.db"
DB_PORT="3306"
DB_NAME="A_CONFIRMER"
DB_USER="A_CONFIRMER"
DB_PASS="A_RENSEIGNER_LOCALEMENT"

SMTP_HOST="ssl0.ovh.net"
SMTP_PORT="587"
SMTP_USER="contact@joie-enseignante.com"
SMTP_PASS="A_RENSEIGNER_LOCALEMENT"

ADMIN_EMAIL="A_CONFIRMER"
EOF
chmod 600 .env.deploy
```

---

## 1. Vérifications avant envoi (PHP 8.4, `vendor/`, extensions PHP)

### 1.1 Vérifier la syntaxe de tous les fichiers PHP et l'absence de dépréciations PHP 8.4
En local, à la racine du projet :

```bash
# Contrôle de syntaxe sur l'ensemble des fichiers PHP du projet
find config includes public admin tools -name "*.php" -print0 \
  | xargs -0 -n1 php -l

# Installer les dépendances de production dans vendor/ (PHPMailer)
composer install --no-dev --optimize-autoloader --no-interaction
```

### 1.2 Vérifier sur OVH la version PHP 8.4 et les extensions requises
Fichier `/home/joieeni/.ovhconfig` attendu sur OVH :

```ini
app.engine=php
app.engine.version=8.4
http.firewall=none
environment=production
container.image=stable64
```

Commande de vérification des extensions PHP requises (`pdo_mysql`, `mbstring`, `curl`, `fileinfo`, `openssl`, `json`, `session`) :

```bash
source .env.deploy
ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" \
  "php -v && php -m | grep -E '^(pdo_mysql|mbstring|curl|fileinfo|openssl|json|session)$'"
```

- **Point de contrôle n°1** :
  - `php -l` retourne `No syntax errors detected` sur tous les fichiers.
  - `vendor/autoload.php` est présent.
  - La commande distante affiche `PHP 8.4.x` et les **7 extensions** (`curl`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `session`).

---

## 2. Configuration de production (fichiers hors Git, `SITE_URL`, debug désactivé, droits)

Le dépôt Git ne contient aucun secret : `config/config.php` charge automatiquement **`config/db_config.php`** (ignoré par Git) et coupe l'affichage des erreurs lorsque `'debug' => false`.

### 2.1 Vérifier `SITE_URL`
```bash
grep -n "define('SITE_URL'" config/theme.php
# Doit afficher : define('SITE_URL', 'https://joie-enseignante.com');
```

### 2.2 Générer les 3 fichiers de configuration sur le serveur (sans les commiter)
Une fois les fichiers envoyés (étape 6), création des fichiers hors Git directement sur OVH à partir de `.env.deploy` :

```bash
source .env.deploy
ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "cat > '$OVH_REMOTE_DIR/config/db_config.php'" <<EOF
<?php
return [
    'host'  => '${DB_HOST}',
    'port'  => '${DB_PORT}',
    'name'  => '${DB_NAME}',
    'user'  => '${DB_USER}',
    'pass'  => '${DB_PASS}',
    'debug' => false,
];
EOF

ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "cat > '$OVH_REMOTE_DIR/config/mail_config.php'" <<EOF
<?php
return [
    'host'       => '${SMTP_HOST}',
    'username'   => '${SMTP_USER}',
    'password'   => '${SMTP_PASS}',
    'port'       => ${SMTP_PORT},
    'encryption' => PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
    'from_email' => '${SMTP_USER}',
    'from_name'  => 'Joie Enseignante',
];
EOF
```

*(Pour `config/payment_config.php`, copier `config/payment_config.example.php` et renseigner les clés Kkiapay via `nano` sur le serveur.)*

### 2.3 Verrouiller les permissions des fichiers et dossiers sur OVH
```bash
source .env.deploy
ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "
  cd '$OVH_REMOTE_DIR' &&
  mkdir -p logs uploads/images uploads/pdf uploads/docs uploads/audio uploads/video uploads/cours/pdf uploads/cours/video &&
  find . -type d -exec chmod 705 {} + &&
  find . -type f -exec chmod 604 {} + &&
  chmod 700 logs &&
  chmod 600 config/*_config.php
"
```

- **Point de contrôle n°2** :
  - `git status --ignored` confirme que `config/db_config.php`, `config/mail_config.php`, `config/payment_config.php` et `.env.deploy` sont ignorés par Git.
  - `php -r 'require "config/config.php"; var_dump(ini_get("display_errors"), SITE_URL);'` sur le serveur affiche `string(1) "0"` et `"https://joie-enseignante.com"`.
  - `ls -l config/*_config.php` sur le serveur affiche `-rw-------` (`600`).

---

## 3. Base de données : création, import de `sql/export_production.sql`, test de connexion

### 3.1 Vérifier que la base est vide puis importer `sql/export_production.sql`
```bash
source .env.deploy

# Envoi temporaire du dump SQL puis import sur la base MySQL OVH
scp -P "$OVH_SSH_PORT" sql/export_production.sql "$OVH_SSH_USER@$OVH_SSH_HOST:/tmp/export_production.sql"

ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "
  MYSQL_PWD='$DB_PASS' mysql -h '$DB_HOST' -P '$DB_PORT' -u '$DB_USER' --default-character-set=utf8mb4 '$DB_NAME' < /tmp/export_production.sql &&
  rm -f /tmp/export_production.sql
"
```

### 3.2 Tester la connexion PDO et compter les 16 tables
```bash
source .env.deploy
ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "
  cd '$OVH_REMOTE_DIR' &&
  php -r 'require \"config/config.php\"; echo \"Tables : \" . count(\$pdo->query(\"SHOW TABLES\")->fetchAll()) . PHP_EOL;'
"
```

- **Point de contrôle n°3** :
  - La commande affiche exactement **`Tables : 16`**.
  - Le fichier temporaire `/tmp/export_production.sql` n'existe plus sur le serveur.

---

## 4. Sécurité du cahier des charges

### 4.1 Remplacer immédiatement l'identifiant et le mot de passe administrateur par défaut
Saisie interactive du nouveau mot de passe administrateur (n'apparaît ni à l'écran ni dans l'historique bash) :

```bash
source .env.deploy
read -rsp "Nouveau mot de passe administrateur pour $ADMIN_EMAIL : " NEW_ADMIN_PASS; echo
ADMIN_HASH=$(php -r 'echo password_hash(getenv("NEW_ADMIN_PASS"), PASSWORD_BCRYPT);' NEW_ADMIN_PASS="$NEW_ADMIN_PASS")
unset NEW_ADMIN_PASS

ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "
  cd '$OVH_REMOTE_DIR' &&
  php -r '
    require \"config/config.php\";
    \$st = \$pdo->prepare(\"UPDATE users SET email = ?, password = ?, reset_token = NULL, reset_expires = NULL WHERE role = '\''admin'\''\");
    \$st->execute([\"$ADMIN_EMAIL\", \"$ADMIN_HASH\"]);
    echo \"Admins mis a jour : \" . \$st->rowCount() . PHP_EOL;
  '
"
unset ADMIN_HASH
```

### 4.2 Vérifier le blocage HTTP (`.htaccess`) des dossiers sensibles et des fichiers payants
Le fichier `.htaccess` bloque `/config/`, `/includes/`, `/sql/`, `/vendor/`, `/logs/`, `/tools/`, les fichiers `.md` / `.sh` / `.env*`, et l'accès direct à `/uploads/(pdf|docs|audio|video|cours)/`.

```bash
for path in \
  "config/config.php" \
  "sql/export_production.sql" \
  "vendor/autoload.php" \
  "logs/" \
  "tools/kkiapay_check.php" \
  "DEPLOIEMENT_OVH_PRO.md" \
  "uploads/pdf/" \
  "uploads/video/" \
  "uploads/cours/pdf/"
do
  code=$(curl -s -o /dev/null -w "%{http_code}" "https://joie-enseignante.com/$path")
  echo "$path -> HTTP $code"
done
```

### 4.3 Vérifier les protections applicatives actives dans le code
- **Validation des fichiers envoyés (*uploads*)** : contrôle de l'extension et du type MIME réel (`finfo_file`) + interdiction d'exécuter du PHP dans `/uploads/` via `.htaccess` (`RewriteRule ^uploads/.*\.(php|phtml|php3|php4|php5|phps)$ - [F,L]`).
- **Limitation des tentatives (*rate-limiting*)** : active en session (`check_rate_limit()` sur `admin/login.php`) et par adresse IP en base (`ip_rate_limits` via `check_ip_rate_limit()` sur `forgot_password.php`, `contact.php`, `retrouver-mon-achat.php`).
- **Protection CSRF des formulaires** : jeton `csrf_token()` vérifié par `verify_csrf()` avec `hash_equals()` sur tous les formulaires POST publics et admin.

- **Point de contrôle n°4** :
  - Toutes les URL sensibles ci-dessus renvoient **HTTP `403`** (ou `404` si non exposées), jamais `200`.
  - L'ancien couple d'identifiants de développement est refusé sur `https://joie-enseignante.com/admin/login.php`.
  - 5 tentatives erronées sur `/admin/login.php` déclenchent le message de blocage temporaire.

---

## 5. E-mail : SMTP `ssl0.ovh.net` port `587`, test d'envoi et SPF / DKIM / DMARC

### 5.1 Vérifier les enregistrements DNS SPF, DKIM et DMARC du domaine
Depuis ton poste :

```bash
# SPF (doit inclure mx.ovh.com)
dig +short TXT joie-enseignante.com | grep -i spf

# DMARC
dig +short TXT _dmarc.joie-enseignante.com

# DKIM OVH (sélecteurs OVH : ovhmo1 / default)
dig +short TXT _domainkey.joie-enseignante.com
```
*(Si DMARC est absent dans **OVH → Domaine → Zone DNS**, ajouter une entrée `TXT` sur `_dmarc.joie-enseignante.com` avec la valeur `"v=DMARC1; p=none; rua=mailto:contact@joie-enseignante.com"`).*

### 5.2 Tester l'envoi SMTP en ligne de commande sur OVH puis via la page Contact
```bash
source .env.deploy
ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "
  cd '$OVH_REMOTE_DIR' &&
  php -r '
    require \"config/config.php\";
    require \"config/mail.php\";
    \$m = getMailer();
    \$m->addAddress(\"${SMTP_USER}\");
    \$m->Subject = \"Test déploiement Joie Enseignante\";
    \$m->Body    = \"Connexion SMTP ssl0.ovh.net:587 opérationnelle.\";
    \$m->send();
    echo \"SMTP OK\" . PHP_EOL;
  '
"
```

- **Point de contrôle n°5** :
  - La commande affiche **`SMTP OK`**.
  - L'e-mail arrive dans la boîte `contact@joie-enseignante.com` (en-têtes `SPF: PASS` et `DKIM: PASS`).
  - Un envoi depuis `https://joie-enseignante.com/public/contact.php` affiche la confirmation verte.

---

## 6. Envoi des fichiers par clé SSH (`rsync`), sans `.git`, sans configs locales ni secrets

### 6.1 Installer ta clé publique SSH sur OVH (une seule fois)
```bash
source .env.deploy
ssh-copy-id -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST"
```

### 6.2 Simuler (`--dry-run`) puis envoyer les fichiers avec `rsync`
La commande exclut `.git/`, `.env*`, les configurations locales (`config/*_config.php`), les scripts et notes internes (`*.md`, `dev-start.sh`, `tools/`), ainsi que les fichiers bruts non utilisés dans `img/` :

```bash
source .env.deploy

# 1. Simulation à blanc (aucun fichier modifié) : vérifier la liste affichée
rsync -avz --dry-run \
  -e "ssh -p $OVH_SSH_PORT" \
  --exclude='.git/' \
  --exclude='.agents/' \
  --exclude='.claude/' \
  --exclude='.env*' \
  --exclude='config/*_config.php' \
  --exclude='logs/*' \
  --exclude='sql/' \
  --exclude='tools/' \
  --exclude='*.md' \
  --exclude='dev-start.sh' \
  --exclude='img/favicone*' \
  --exclude='img/logo *' \
  --exclude='img/logo.jpg' \
  ./ "$OVH_SSH_USER@$OVH_SSH_HOST:$OVH_REMOTE_DIR/"

# 2. Envoi réel (après validation de la simulation)
rsync -avz \
  -e "ssh -p $OVH_SSH_PORT" \
  --exclude='.git/' \
  --exclude='.agents/' \
  --exclude='.claude/' \
  --exclude='.env*' \
  --exclude='config/*_config.php' \
  --exclude='logs/*' \
  --exclude='sql/' \
  --exclude='tools/' \
  --exclude='*.md' \
  --exclude='dev-start.sh' \
  --exclude='img/favicone*' \
  --exclude='img/logo *' \
  --exclude='img/logo.jpg' \
  ./ "$OVH_SSH_USER@$OVH_SSH_HOST:$OVH_REMOTE_DIR/"
```

- **Point de contrôle n°6** :
  - `rsync --dry-run` ne liste aucun fichier `*_config.php`, `.env*` ni `.git/`.
  - Sur le serveur, `ls -la "$OVH_REMOTE_DIR/.git"` renvoie `No such file or directory`.
  - Les dossiers `assets/`, `public/`, `admin/`, `includes/`, `vendor/` et `uploads/` (avec leurs `.htaccess`) sont présents dans `$OVH_REMOTE_DIR`.

---

## 7. Recette : liste de contrôle page par page

Vérification automatique des codes HTTP, suivie du contrôle visuel :

```bash
for url in \
  "https://joie-enseignante.com/" \
  "https://joie-enseignante.com/public/index.php" \
  "https://joie-enseignante.com/public/publications.php" \
  "https://joie-enseignante.com/public/post.php?id=1" \
  "https://joie-enseignante.com/public/cours.php" \
  "https://joie-enseignante.com/public/course.php?id=1" \
  "https://joie-enseignante.com/public/chapter.php?id=1" \
  "https://joie-enseignante.com/public/biography.php" \
  "https://joie-enseignante.com/public/about.php" \
  "https://joie-enseignante.com/public/contact.php" \
  "https://joie-enseignante.com/public/retrouver-mon-achat.php" \
  "https://joie-enseignante.com/public/mentions-legales.php" \
  "https://joie-enseignante.com/public/confidentialite.php" \
  "https://joie-enseignante.com/public/conditions-utilisation.php" \
  "https://joie-enseignante.com/sitemap.xml" \
  "https://joie-enseignante.com/admin/login.php"
do
  printf "%-65s -> HTTP %s\n" "$url" "$(curl -s -L -o /dev/null -w '%{http_code}' "$url")"
done
```

| Page / Fonction | URL | Ce qu'il faut vérifier |
|---|---|---|
| **Accueil** | `/public/index.php` | Carrousel, logo gris (clair) / blanc (sombre), favicon dans l'onglet, formulaire newsletter |
| **Publications** | `/public/publications.php` | Filtres par catégorie, recherche, cartes d'articles |
| **Article & Document** | `/public/post.php?id=1` | Lecture de l'article, bouton de téléchargement (gratuit) ou bouton d'achat (`purchase.php` si payant), commentaires |
| **Cours** | `/public/cours.php` et `/public/course.php?id=1` | Liste des cours, chapitres en « Aperçu gratuit » accessibles sur `chapter.php`, chapitres payants verrouillés sans jeton |
| **Biographie & À propos** | `/public/biography.php`, `/public/about.php` | Photos, parcours académique, statistiques |
| **Contact** | `/public/contact.php` | Coordonnées issues de `settings`, envoi réel d'un message |
| **Retrouver mon achat** | `/public/retrouver-mon-achat.php` | Saisie d'un e-mail → envoi des liens d'accès |
| **Administration** | `/admin/login.php` → `/admin/dashboard.php` | Connexion avec le nouvel e-mail/mot de passe, barre latérale fixe sans défilement, Articles, Cours, Ventes + export CSV, Newsletter, Réglages du site, Journal des actions |

- **Point de contrôle n°7** :
  - Toutes les URL de la boucle renvoient **HTTP `200`**.
  - Aucune erreur dans la console du navigateur (F12) ni dans `logs/` sur le serveur.

---

## 8. Sauvegarde avant envoi et procédure de retour arrière (*rollback*)

### 8.1 Sauvegarde complète avant tout envoi (fichiers + base MySQL)
À lancer **avant** chaque mise en production ou mise à jour :

```bash
source .env.deploy
STAMP=$(date +%Y%m%d_%H%M%S)

ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "
  mkdir -p ~/backups &&
  tar -czf ~/backups/www_${STAMP}.tar.gz -C '$OVH_REMOTE_DIR' . 2>/dev/null || true &&
  MYSQL_PWD='$DB_PASS' mysqldump -h '$DB_HOST' -P '$DB_PORT' -u '$DB_USER' \
    --single-transaction --routines --default-character-set=utf8mb4 '$DB_NAME' \
    | gzip > ~/backups/db_${STAMP}.sql.gz &&
  chmod 600 ~/backups/*_${STAMP}.* &&
  ls -lh ~/backups/*_${STAMP}.*
"
```

### 8.2 Procédure de retour arrière (*rollback*) en 1 minute en cas de problème
Si une anomalie est constatée après l'envoi, restaurer immédiatement l'état précédent (`STAMP` = horodatage de la sauvegarde) :

```bash
source .env.deploy
STAMP="REMPLACER_PAR_HORODATAGE"

ssh -p "$OVH_SSH_PORT" "$OVH_SSH_USER@$OVH_SSH_HOST" "
  # 1. Restaurer les fichiers du dossier web
  find '$OVH_REMOTE_DIR' -mindepth 1 -maxdepth 1 -exec rm -rf {} + &&
  tar -xzf ~/backups/www_${STAMP}.tar.gz -C '$OVH_REMOTE_DIR' &&

  # 2. Restaurer la base de données MySQL
  gunzip -c ~/backups/db_${STAMP}.sql.gz \
    | MYSQL_PWD='$DB_PASS' mysql -h '$DB_HOST' -P '$DB_PORT' -u '$DB_USER' '$DB_NAME'
"
```

- **Point de contrôle n°8** :
  - Les deux archives `~/backups/www_<STAMP>.tar.gz` et `~/backups/db_<STAMP>.sql.gz` existent hors du dossier public (`/home/joieeni/backups`, inaccessible depuis le web) avec une taille non nulle et les droits `600`.
