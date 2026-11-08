# Guide de déploiement pas-à-pas — OVH Pro (`joie-enseignante.com`)

Ce guide est préparé sur mesure à partir de ta facture OVH (`SN105018` — client `cx69363-ovh`).

---

## Récapitulatif de tes paramètres OVH

| Élément | Valeur exacte |
|---|---|
| **Domaine** | `https://joie-enseignante.com` (et `www.joie-enseignante.com`) |
| **Hébergement** | `joieeni.cluster130.hosting.ovh.net` (offre **Hosting Pro**, datacenter `GRA1`) |
| **Serveur SSH / SFTP** | `ssh.cluster130.hosting.ovh.net` (port `22`) |
| **Serveur FTP** | `ftp.cluster130.hosting.ovh.net` (port `21`) |
| **Identifiant FTP / SSH** | `joieeni` |
| **Dossier web sur le serveur** | `/home/joieeni/www` |
| **Serveur SMTP (e-mails)** | `ssl0.ovh.net` — port `587` (STARTTLS) |
| **Boîte e-mail du site** | `contact@joie-enseignante.com` (offre **MXPlan 100** incluse) |

---

## Étape 1 — Préparer l’espace client OVH (10 minutes)

Connecte-toi sur <https://www.ovhcloud.com/fr/> avec l’identifiant **`cx69363-ovh`**.

### 1.1 Vérifier la version de PHP (PHP 8.2 ou 8.3)
1. Va dans **Web Cloud → Hébergements → `joieeni.cluster130.hosting.ovh.net`**.
2. Sur l’onglet **Informations générales**, regarde **Version de PHP globale** :
   - Si ce n’est pas **8.2** ou **8.3**, clique sur `...` → **Modifier la configuration** → choisis **PHP 8.2** (ou **8.3**) et **Environnement : Production**.

### 1.2 Activer le cadenas HTTPS (SSL Let’s Encrypt)
1. Onglet **Multisite**.
2. Vérifie que les lignes `joie-enseignante.com` et `www.joie-enseignante.com` pointent vers le dossier Dossier racine **`www`** et ont **SSL = Activé**.
3. Si SSL est désactivé : clique sur **Commander un certificat SSL** → **Certificat gratuit Let’s Encrypt** (l’activation prend environ 15 minutes).

### 1.3 Activer le SSH pour déployer rapidement
1. Onglet **FTP - SSH**.
2. Sur la ligne de l’utilisateur principal **`joieeni`** :
   - Passe la colonne **SSH** sur **Activé**.
   - Si tu n’as pas le mot de passe, clique sur l’icône crayon pour définir ton mot de passe FTP/SSH.

### 1.4 Créer la base de données MySQL
1. Onglet **Bases de données** → bouton **Créer une base de données**.
2. Choisis **MySQL 8.0** (ou **8.4**), donne un suffixe (ex. `joieeni` → le nom complet sera du type `joieeniprod`) et définis un mot de passe.
3. Note bien ces **4 informations** :
   - `DB_HOST` : ex. `joieeniprod.mysql.db` *(sur OVH, ce n’est jamais `localhost`, c’est toujours `xxx.mysql.db`)*
   - `DB_NAME` : ex. `joieeniprod`
   - `DB_USER` : ex. `joieeniprod`
   - `DB_PASS` : le mot de passe choisi

### 1.5 Créer la boîte e-mail `contact@joie-enseignante.com`
1. Dans le menu gauche d’OVH : **Web Cloud → E-mails → `joie-enseignante.com`**.
2. Onglet **Gestion des comptes → Créer un compte e-mail**.
3. Crée **`contact@joie-enseignante.com`** avec un mot de passe solide.
4. Dans l’onglet **Zone DNS** du domaine `joie-enseignante.com`, vérifie que l’entrée **SPF** (`v=spf1 include:mx.ovh.com ~all`) est bien présente (OVH la met par défaut avec MXPlan).

---

## Étape 2 — Envoyer le code sur OVH en SSH (méthode recommandée, 2 minutes)

Depuis un terminal sur ton ordinateur, connecte-toi au serveur OVH :

```bash
ssh joieeni@ssh.cluster130.hosting.ovh.net
```

Une fois connecté sur le serveur OVH, exécute ces commandes :

```bash
cd ~/www

# 1. Supprimer la page "Félicitations OVH" par défaut si le dossier contient index.html
rm -f index.html

# 2. Cloner le projet depuis GitHub dans le dossier courant (www)
git clone https://github.com/Mouphid/prof-chez-nous.git .

# 3. Installer PHPMailer (dépendance PHP pour envoyer les e-mails)
composer install --no-dev --optimize-autoloader

# 4. Créer le dossier de journaux s'il n'existe pas
mkdir -p logs
chmod 750 logs
```

### Envoyer le dossier `uploads/` (PDF, vidéos et images existants)

Depuis ton **ordinateur local** (dans un autre terminal, à la racine du projet) :

```bash
scp -r uploads joieeni@ssh.cluster130.hosting.ovh.net:~/www/
```

---

## Étape 3 — Importer la base de données (`sql/export_production.sql`)

Tu as deux façons de faire (au choix) :

### Option A — En 1 ligne depuis le terminal SSH OVH (le plus rapide)
Sur le serveur OVH (`~/www`) :

```bash
mysql -h REMPLACER_PAR_DB_HOST.mysql.db -u REMPLACER_PAR_DB_USER -p REMPLACER_PAR_DB_NAME < sql/export_production.sql
```
*(Il te demandera le mot de passe de la base MySQL, puis importera les 16 tables.)*

### Option B — Via phpMyAdmin dans l’espace OVH
1. Dans **OVH → Hébergements → Bases de données**, clique sur `...` à droite de ta base → **Accéder à phpMyAdmin**.
2. Clique sur l’onglet **Importer** → choisis le fichier **`sql/export_production.sql`** → **Exécuter**.

---

## Étape 4 — Créer les 3 fichiers de configuration sur le serveur

Toujours sur le serveur OVH dans `~/www` :

### 4.1 Connexion à la base de données (`config/config.php`)
En haut de `config/config.php`, remplace les 4 valeurs par défaut (lignes 4 à 8) par celles de ta base OVH :

```php
$host = getenv('DB_HOST') ?: 'joieeniprod.mysql.db'; // <- serveur MySQL OVH
$port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME') ?: 'joieeniprod';       // <- nom de la base OVH
$username = getenv('DB_USER') ?: 'joieeniprod';      // <- utilisateur MySQL OVH
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'TON_MOT_DE_PASSE_MYSQL';
```

### 4.2 Configuration des e-mails (`config/mail_config.php`)

```bash
cp config/mail_config.example.php config/mail_config.php
nano config/mail_config.php
```

Il est déjà pré-rempli pour OVH (`ssl0.ovh.net`, port `587`, `contact@joie-enseignante.com`). Il te suffit de remplacer **`MOT_DE_PASSE_DE_LA_BOITE_OVH`** par le mot de passe de la boîte e-mail créée à l’étape 1.5.

### 4.3 Configuration du paiement (`config/payment_config.php`)

```bash
cp config/payment_config.example.php config/payment_config.php
nano config/payment_config.php
```

- **Pendant que Kkiapay valide le dossier marchand (KYC)** : laisse `'sandbox' => true` avec tes clés Sandbox, ou laisse les articles à `0 FCFA` (gratuits).
- **Dès que les clés Live sont disponibles** :
  - passe `'sandbox' => false` ;
  - colle `public_key`, `private_key` et `secret_key` Live ;
  - dans **Kkiapay → Développeurs → Webhook**, indique :
    ```
    https://joie-enseignante.com/public/payment_webhook.php
    ```

---

## Étape 5 — Les 6 vérifications finales après mise en ligne

1. **Ouverture du site en HTTPS** :
   - Ouvre `https://joie-enseignante.com` → la page d’accueil s’affiche avec le cadenas vert.
2. **Protection des documents et fichiers sensibles (`.htaccess`)** :
   - Ouvre `https://joie-enseignante.com/uploads/pdf/` ou `https://joie-enseignante.com/SPECS_FAVICON.md` → tu dois obtenir une erreur **403 Forbidden** (accès interdit).
3. **Connexion à l’administration + changement immédiat du mot de passe** :
   - Ouvre `https://joie-enseignante.com/admin/login.php`
   - Connecte-toi avec `prof@joieenseignante.com` / `Admin2026!`
   - Va tout de suite dans **Paramètres** et change ce mot de passe.
4. **Réglages du site** :
   - Va dans **Admin → Réglages du site** et renseigne le vrai numéro de téléphone et les vrais liens réseaux sociaux du professeur.
5. **Test d’envoi d’e-mail** :
   - Envoie un message depuis `https://joie-enseignante.com/public/contact.php` (ou teste « Mot de passe oublié » sur l’admin) pour vérifier que `ssl0.ovh.net` livre bien l’e-mail.
6. **Plan du site pour Google** :
   - Vérifie que `https://joie-enseignante.com/sitemap.xml` affiche bien le XML des pages et articles.

---

## Pour mettre à jour le site plus tard (en 10 secondes)

Grâce à Git sur OVH Pro, chaque future modification se déploie en une seule commande SSH :

```bash
ssh joieeni@ssh.cluster130.hosting.ovh.net "cd ~/www && git pull origin master"
```
