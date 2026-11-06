# 🔍 AUDIT TECHNIQUE — Projet « Joie Enseignante »

**Date :** 28 septembre 2026
**Objet :** État des lieux complet du site avant mise en ligne (préparatoire au cahier des charges)

---

## 1. Synthèse

| Critère | État |
|---|---|
| Fonctionnalités publiques | ✅ Opérationnel |
| Espace admin | ✅ Opérationnel (connexion, articles, catégories, commentaires, utilisateurs) |
| Sécurité | 🟢 Bon niveau (CSRF, bcrypt, rate-limit, sessions, .htaccess) |
| Performance | 🟡 Correct — CSS compilé unique, images non optimisées |
| SEO | 🟡 Partiel — OG seulement sur les articles, pas de sitemap/robots |
| Accessibilité | 🟢 Très bon (skip-links, aria-labels, rôles) |
| Bug(s) bloquant(s) | 🔴 5 fichiers redirigent vers l'accueil → fonctionnalités désactivées |
| Dépendances | 🟢 PHPMailer seul (léger) |

---

## 2. Stack technique

- **Backend :** PHP 8.3 — **sans framework** (PDO préparé, sessions natives), MySQL 8 (base `joieenseignante`)
- **Frontend :** HTML/CSS/JS vanilla, **Tailwind compilé** (`assets/css/tailwind.css` + fallback `sobre.css`), icônes **Phosphor**, polices Inter (auto-hébergée)
- **Mail :** PHPMailer v6.10 via `config/mail.php` (config SMTP non versionnée : `mail_config.example.php`)
- **Stockage :** fichiers uploads (`uploads/images`, `pdf`, `docs`) ; fichiers dans `img/bg/` (images de bannière/hero)
- **Déploiement :** Apache (`.htaccess` actif), compatible nginx avec réécritures équivalentes
- **Sécurité sessions :** `httponly`, `SameSite=Lax`, secure si HTTPS, session idle 30 min

---

## 3. Fonctionnalités recensées

### 3.1 Espace public (utilisateur)
- Accueil avec **hero slider** (3 images, dots, auto 6 s) + voile sombre `bg-dark/50`
- **Articles** : listing paginé (4/page), page article (compteur de vues, article_reads pour « déjà lu »), catégories, recherche (`search.php`), pagination
- **Cours** (`cours.php`) et **Publications** (`publications.php`) : listes de ressources avec fichiers PDF
- **Biographie** (renommée de « Profil ») : présentation de M. Sylvestre Djouamon
- **Contact** : formulaire avec CSRF + validation email → envoi PHPMailer à l'admin
- **Commentaires** : ajout (modération : statut pending/visible/hidden), chargement AJAX (`fetch_comments.php`), gestion de son propre commentaire (`manage_comment.php`)
- **Likes** : AJAX (`like_post.php`) avec protection CSRF + cooldown par IP
- **Newsletter** : formulaire (redirige vers contact) avec promesse « sans spam, 1 email/semaine, désinscription 1 clic »
- **Thème sombre** : toggle + persistance localStorage + préférence système
- Pages légales : mentions légales, confidentialité, conditions d'utilisation

### 3.2 Espace admin (`/admin/`)
- **Connexion** sécurisée : CSRF + rate-limit (5 essais / 5 min) + bcrypt + `session_regenerate_id` + log
- **Permissions par rôle** : `admin`, `auteur`, `etudiant` (PERMISSIONS dans `config_admin.php`)
- **Dashboard** : stats (articles, publiés, brouillons, programmés, commentaires, vues, likes, catégories, nouveaux 30 j)
- **Gestion** : articles (créer/éditer/supprimer, image, vidéo, fichiers), catégories, commentaires (validation), utilisateurs (rôles), fichiers

### 3.3 Base de données (9 tables)
`users`, `categories`, `posts`, `files`, `comments`, `likes`, `settings`, `article_reads`, `user_downloads`
- Données initiales : 1 compte admin, catégories pré-remplies, 21 articles/posts, ~18 PDF dans uploads

---

## 4. Sécurité — ce qui est fait (🟢)

- ✅ **Prévention SQLi** : quasi exclusivement des requêtes préparées (`PDO::prepare/execute`, `ATTR_EMULATE_PREPARES=false`). Les seules concaténations sont sur des valeurs **castées en int** (`(int)$_SESSION['admin_id']`, `(int)$_GET['id']`) → sûres.
- ✅ **XSS** : sorties échappées (`htmlspecialchars`) sur les données utilisateur ; aucun `echo $_GET/POST` brut.
- ✅ **CSRF** : token par session (`csrf_token()`/`verify_csrf()`) vérifié sur les formulaires d'action (login, contact, commentaires, likes, admin).
- ✅ **Passwords** : `password_hash(PASSWORD_BCRYPT)` + `password_verify` (jamais de md5/sha1).
- ✅ **Uploads** : validation **MIME réelle** (`finfo`) + extension dans une liste blanche + renommage `time()` ; `.htaccess` bloque l'exécution PHP dans `uploads/`.
- ✅ **Téléchargements** : `basename()` (anti path traversal), types en liste blanche, `download.php` n'est pas accessible en exécution directe.
- ✅ **Headers** : X-Frame-Options DENY, nosniff, XSS-Protection, Referrer-Policy (`.htaccess`).
- ✅ **Sensibles bloqués** : `config|includes|sql|vendor|logs` en 403, fichiers pointés (composer, README, .*) refusés.
- ✅ **Devinettes** : pas de page « install.php », pas de `config/mail_config.php` versionné (gitignore), logs ignorés.
- ✅ **Session** : régénération d'ID après login, idle timeout 30 min, cookie httponly/SameSite.

### Faiblesses mineures (à corriger — non bloquantes)
- ⚠️ **Pas de CSP** (Content-Security-Policy) — recommandation de durcissement progressive.
- ⚠️ **CSRF manquant** sur `manage_comment.php` / suppression (à vérifier — à ajouter systématiquement).
- ⚠️ **Rate-limit** de connexion public basé sur **session** (contournable en changeant de session) — un rate-limit par IP serait plus robuste en production.
- ⚠️ `mail.php` vers `sequoiaempire@gmail.com` codé en dur dans `contact.php` — à externaliser en config.

---

## 5. 🔴 BUGS — Fonctionnalités désactivées (avant mise en ligne)

Ces fichiers commencent par `header("Location: index.php"); exit;` → **inaccessibles** :

| Fichier | Fonction | Impact |
|---|---|---|
| `public/download.php` | Téléchargement sécurisé des fichiers | ❌ Impossible de télécharger les PDF |
| `public/register.php` | Inscription utilisateur | ❌ Impossible de s'inscrire |
| `public/my_comments.php` | Historique de mes commentaires | ❌ Page vide (redirection) |
| `public/my_downloads.php` | Historique de mes téléchargements | ❌ Page vide |
| `public/profile.php` | Profil personnel | ❌ Page vide |

> ⚠️ **Conséquence directe** : le site est en lecture seule avec un seul auteur admin (c'est le choix actuel : blog d'un professeur). **Décision à acter** : soit on réactive ces fonctionnalités (compte visiteur complet), soit on les retire définitivement du menu et du code pour un site « vitrine admin seul ». Cette décision doit figurer dans le cahier des charges.

---

## 6. Performance

- ✅ Un seul CSS compilé (~90 Ko tailwind + sobre.css), un seul JS, icônes en font (1 requête), police Inter woff2
- ✅ `loading="lazy"` sur les images de la liste d'articles
- ✅ PHP sans framework → léger, MySQL local
- ⚠️ **Images non compressées** : les PNG/JPG uploads (photos professeur, bannières) sont brutes (certaines > 1 Mo) — à redimensionner/optimiser avant mise en ligne
- ⚠️ Pas de cache HTTP/CSS (cache-control) configuré
- ⚠️ Fonts Inter auto-hébergées = ~60 fichiers woff2 (toutes variantes) — correct pour la prod, aucun Google Fonts externe

---

## 7. SEO / Référencement

| Élément | État |
|---|---|
| `title` + `meta description` par page | ✅ |
| Open Graph (articles) | 🟡 uniquement sur `post.php` |
| Sitemap.xml / robots.txt | ❌ Absents |
| URLs propres (slugs) | 🟡 existe `slug` en BDD mais l'URL publique reste `post.php?id=` |
| Canonical | ❌ Absent |
| Données structurées (schema.org Article) | ❌ Absentes |
| domaine `.com` | ✅ choisi (voir devis) |

---

## 8. Accessibilité & responsive

- ✅ Skip-link « Aller au contenu », aria-labels sur boutons/nav, `lang="fr"`
- ✅ Formulaires avec `<label for>`, contrastes vérifiés (dark + light), focus visibles
- ✅ Mobile : menu hamburger, grilles responsive (sm/lg breakpoints), hero adaptatif
- ⚠️ À vérifier : contrastes des badges `accent-400` sur fond clair (audit WCAG AA complet à refaire sur le site réel en ligne)

---

## 9. Maintenance hébergement (rappel chiffrage)

- Hébergement **Pro OVHcloud** (PHP 8.x + MySQL 8 natifs, 250 Go, SSL inclus) — 24 mois
- Domaine `joiedenseignante.com` — 2 ans
- Configure `config/config.php` via **variables d'environnement** (DB_*, déjà prévu) → parfait pour la prod
- SMTP : configurer `mail_config.php` réellement (expected pour l'envoi contact/inscription)

---

## 10. Récapitulatif des actions conseillées avant mise en ligne

| # | Action | Priorité |
|---|---|---|
| 1 | **Trancher** le sort de register/my_comments/my_downloads/profile/download (réactiver ou masquer + retirer du code) | Haute |
| 2 | Optimiser/compresser les images uploads (bannières, photos, PNG) | Haute |
| 3 | Ajouter sitemap.xml + robots.txt + canonical + OG sur toutes les pages | Moyenne |
| 4 | Ajouter un header CSP de base + rate-limit par IP | Moyenne |
| 5 | Externaliser l'email admin de contact.php en config | Moyenne |
| 6 | Ajouter cache-control sur assets statiques | Basse |
| 7 | Configurer SMTP réel en production | Haute |

---

*Audit fondé sur la lecture du code et de la base (28 sept 2026). Les tests fonctionnels réels (navigation multi-pages, soumission formulaires, envoi mails) restent à effectuer sur l'instance en ligne après déploiement.*