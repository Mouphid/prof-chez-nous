# 📌 Suivi des fonctionnalités à intégrer

**Projet :** Joie Enseignante — `joiedenseignante.com`
**Créé le :** 6 novembre 2026
**Dernière mise à jour :** 7 novembre 2026

Ce fichier est la **liste de contrôle** des fonctionnalités qui ne sont pas encore en place.
Il évite de les oublier et sert de feuille de route le jour de l'intégration.

## Comment l'utiliser

1. Quand une fonctionnalité est terminée : passer son état à **✅ Fait**, ajouter la date et le commit Git dans le tableau **Historique** en bas.
2. Si un point est en cours : état **🔄 En cours**.
3. Ne rien supprimer : on garde la trace, même une fois fait.

**Légende :** ⏳ À faire · 🔄 En cours · ✅ Fait · ⏸ En attente d'une décision ou d'un tiers

---

## A. Priorité 1 — Avant la mise en ligne (sécurité)

### A1. Protéger les fichiers de `uploads/` ✅ Fait (6 nov. 2026)
- **Constat initial :** les fichiers étaient accessibles par une adresse directe
  (`/uploads/pdf/article 4.pdf` → **HTTP 200, 5,7 Mo**), ce qui contournait le paiement.
- **Correction :** accès direct bloqué pour `uploads/pdf`, `uploads/docs`, `uploads/audio`
  et `uploads/video`, à deux niveaux : règle générale dans `.htaccess` + fichier `.htaccess`
  dans chaque dossier. `uploads/images` reste public.
- **Vidéos :** servies par `public/video.php`, uniquement si elles appartiennent à un article
  visible, avec support des requêtes partielles (lecture et déplacement dans la vidéo).
- **Fichiers :** `.htaccess`, `uploads/*/.htaccess`, `public/video.php`, `public/post.php`
- **Commit :** `c1043a3`
- ⚠️ **À vérifier en production (Apache/OVH) :**
  `curl -I https://joiedenseignante.com/uploads/pdf/<un-fichier>.pdf` doit renvoyer **403**.
  Le serveur local de test (`php -S`) ignore les `.htaccess` : ce contrôle ne peut se faire qu'en ligne.

### A2. Message honnête si l'e-mail de contact échoue ✅ Fait (6 nov. 2026)
- **Constat initial :** le site affichait « Votre message a été envoyé avec succès » même quand l'envoi échouait.
- **Correction :** message d'erreur explicite avec l'adresse de secours, erreur journalisée ;
  `config/mail.php` lève maintenant une erreur claire si PHPMailer ou le SMTP manque.
- **Fichiers :** `public/contact.php`, `config/mail.php`
- **Commit :** `c1043a3`

---

## B. Priorité 2 — Achats sans compte visiteur

> Rappel : **aucun compte visiteur**. L'acheteur paie, le serveur vérifie, un lien privé est généré.

### B1. Envoyer le lien d'achat par e-mail ✅ Fait (6 nov. 2026)
- **Constat initial :** après paiement, le lien n'existait que dans la redirection. Si l'acheteur fermait la page, il perdait l'accès.
- **Réalisé :** après confirmation du paiement, un e-mail part automatiquement à l'adresse saisie,
  avec le nom du document, le montant réglé et un bouton de téléchargement.
- **Robustesse :**
  - l'e-mail n'est envoyé **qu'une fois** (le webhook et le retour navigateur ne le doublent pas) ;
  - si l'envoi échoue, l'erreur est journalisée et **le téléchargement reste accessible** ;
  - le contenu est préparé par `build_purchase_email()`, séparé de l'envoi pour être vérifié sans SMTP.
- **Fichiers :** `public/payment_callback.php`, `config/mail.php`
- ⚠️ **Envoi réel en attente du SMTP** (voir G4) : sans `config/mail_config.php`, l'e-mail échoue
  proprement et l'acheteur garde son lien immédiat.

### B2. Renvoyer un lien depuis l'admin ✅ Fait (6 nov. 2026)
- **Constat initial :** l'écran Ventes affichait les achats mais ne pouvait pas renvoyer l'accès.
- **Réalisé :**
  - bouton **« Renvoyer »** sur chaque vente payée, avec confirmation avant envoi ;
  - **recherche** par nom, e-mail ou nom du document ;
  - message clair après l'envoi : succès, ou erreur honnête (« PHPMailer n'est pas installé », SMTP absent, etc.) ;
  - la recherche et le filtre de statut sont conservés après le renvoi.
- **Fichiers :** `admin/manage_purchases.php`, `config/mail.php`
- ⚠️ **Envoi réel en attente du SMTP** (voir G4).

### B3. Page « Retrouver mon achat » ✅ Fait (6 nov. 2026)
- **Réalisé :** page `public/retrouver-mon-achat.php` : l'acheteur saisit l'adresse utilisée au paiement
  et reçoit **un seul e-mail** regroupant tous ses liens (documents, cours complets, chapitres).
- **Sans compte :** le lien reçu reste la clé d'accès ; aucun mot de passe à retenir.
- **Anti-abus :**
  - 3 demandes par heure pour une même adresse, 10 par heure pour une même connexion ;
  - table `purchase_recovery_requests` : seules des empreintes (hash) sont stockées, jamais l'adresse en clair ;
  - piège à robots (champ caché) ;
  - message toujours identique : le site ne révèle jamais si une adresse a acheté quelque chose.
- **Liens d'accès :** pied de page, page Contact, pages d'achat et e-mails d'achat.
- **Fichiers :** `public/retrouver-mon-achat.php`, `config/mail.php`, `sql/migration_recuperation.sql`,
  `includes/footer.php`, `public/contact.php`, `public/purchase.php`, `public/purchase_course.php`
- ⚠️ **Envoi réel en attente du SMTP** (voir G4), comme pour B1/B2.
- **Commit :** à venir

### B4. Inscription / Connexion : **on garde** ✅ Décision du 6 nov. 2026
- **Décision :** ne pas masquer. Les comptes vont servir plus tard aux **contributeurs
  professionnels** (autres professeurs) : voir B6.
- **Déjà corrigé :** le lien de vérification d'e-mail ne contient plus de chemin en dur
  (`/JoieEnseignante/…`) ; il utilise `SITE_URL`.
- **Fichiers :** `public/register.php`, `public/resend-verification.php`

### B5. Mot de passe oublié (admin) ✅ Fait (7 nov. 2026)
- **Constat :** aucune page de récupération. Si le mot de passe admin est perdu, l'accès est bloqué.
- **Réalisé :**
  - page `admin/forgot_password.php` : l'administrateur saisit son adresse, reçoit un lien valable **1 heure** ;
  - page `admin/reset_password.php` : choix d'un nouveau mot de passe (10 caractères, majuscule, minuscule, chiffre, caractère spécial) ;
  - le **jeton n'est jamais stocké en clair** : seule son empreinte SHA-256 est en base, avec une date d'expiration ;
  - le jeton est **effacé après usage** : il ne peut pas servir deux fois ;
  - message toujours identique (ne révèle pas si l'adresse existe) ;
  - limite de 5 demandes / 5 minutes par adresse IP ;
  - lien « Mot de passe oublié ? » sur la page de connexion.
- **Fichiers :** `admin/forgot_password.php`, `admin/reset_password.php`, `admin/login.php`,
  `config/mail.php`, `sql/migration_mot_de_passe_admin.sql`
- **Commit :** `797f222`
- ⚠️ **Envoi réel en attente du SMTP** (voir G4), comme pour les autres e-mails.

### B6. Espace contributeurs professionnels ⏳ (évolution prévue)
- **Objectif :** permettre à d'autres professeurs de créer des contenus professionnels
  et d'interagir (articles, cours, commentaires), avec validation par l'administrateur.
- **À faire :**
  1. rôles clairs : **admin**, **professeur contributeur**, **lecteur** ;
  2. inscription sur invitation ou validation manuelle du compte ;
  3. espace « Mes contenus » (l'auteur ne voit que ses articles — déjà corrigé dans le tableau de bord) ;
  4. modération des publications et des commentaires ;
  5. charte de publication et page de profil publique.
- **Durée :** 2 à 3 jours
- **Dépend de :** SMTP, décision sur la validation des comptes
- **Fichiers :** `public/register.php`, `admin/manage_users.php`, `admin/config_admin.php`, `admin/dashboard.php`

---

## C. Phase 2 — Cours en ligne payants

> Décision validée : **sans compte**, lien privé envoyé par e-mail après paiement.

### C1. Table `courses` + écran d'administration ✅ Fait (6 nov. 2026)
- **Fait :** tables `courses` et `course_items` (chapitres avec PDF et vidéo, prix, aperçu gratuit) ;
  `purchases` accepte désormais un document, un cours complet ou un chapitre.
- **Admin :** `manage_courses.php`, `add_course.php`, `edit_course.php`, `delete_course.php`
  (création, modification, upload et remplacement des fichiers, suppression bloquée si déjà vendu).
- **Fichiers :** `sql/migration_cours.sql`, `sql/seed_cours_demo.sql`
- **Commits :** `ab065b3`, `4688d7e`

### C2. Vente du cours ou d'un chapitre ✅ Fait (6 nov. 2026)
- **Fait :** page `purchase_course.php` pour acheter le cours complet ou un chapitre seul.
- **Sécurité :** le prix est relu en base, la transaction est revérifiée côté serveur
  et une transaction ne peut pas servir deux fois.
- **Commit :** `b85530b`

### C3. Accès protégé aux fichiers ✅ Fait (6 nov. 2026)
- **Vidéos d'articles :** `public/video.php` (fait précédemment).
- **Cours :** PDF et vidéos passent par `public/course_file.php`, qui vérifie si le chapitre
  est gratuit, en aperçu, ou acheté. Lecture partielle des vidéos prise en charge.
- **Protection :** `uploads/cours/pdf` et `uploads/cours/video` interdits en accès direct (`.htaccess`).
- **Commits :** `938e4c2`, `b85530b`

### C4. E-mail d'accès et suivi des ventes ✅ Fait (6 nov. 2026)
- **Fait :** e-mail automatique avec lien privé (`build_course_email()`, `send_course_access_email()`).
- **Admin :** les ventes de cours apparaissent dans Admin → Ventes, avec recherche
  (nom, e-mail, contenu) et bouton « Renvoyer ».
- **Sans compte :** le lien reçu par e-mail sert de clé d'accès.
- **Commit :** `8c3ebea`

### C5. Espace « Mes cours » ⏸ (seulement si nécessaire plus tard)
- **À faire :** comptes visiteurs + historique d'achats.
- **Décision :** uniquement si le volume de clients le justifie.

---

## D. Newsletter

### D1. Envoi réel des e-mails ✅ Fait (6 nov. 2026)
- **Fait :** écran `admin/send_newsletter.php` : rédaction, **aperçu avant envoi**, envoi réel aux abonnés actifs, historique dans la table `newsletter_campaigns`.
- **Détail :** message en texte simple (pas de HTML), un paragraphe par double saut de ligne ; chaque e-mail contient le lien de désinscription.
- **Dépend de :** SMTP (à configurer en production ; sans SMTP, l'erreur est affichée clairement).
- **Commit :** `470f596`

### D2. Lien de désinscription ✅ Fait (6 nov. 2026)
- **Fait :** jeton unique de 64 caractères par abonné (`unsubscribe_token`), page publique `public/desinscription.php`, confirmation obligatoire (POST + jeton de sécurité), adresse masquée, page non indexée par les moteurs.
- **Détail :** la réinscription réactive l'adresse sans changer son jeton ; le lien déjà utilisé affiche « déjà désinscrit ».
- **Commit :** `470f596`

### D3. Écran admin + export ✅ Fait (6 nov. 2026)
- **Fait :** `admin/manage_newsletter.php` : nombre total / actifs / désinscrits, filtres, recherche, pagination (50 par page), désinscrire ou réactiver, 5 derniers envois.
- **Export :** `admin/export_subscribers.php` en CSV (UTF-8 avec BOM, séparateur `;`, protection contre les formules Excel), en respectant les filtres.
- **Commit :** `470f596`

---

## E. Confort d'administration

| # | Fonctionnalité | État | Durée | Détail |
|---|---|---|---|---|
| E1 | Écran **Réglages du site** | ✅ | 1/2 journée | `admin/site_settings.php` : e-mail, téléphone, adresse et réseaux sociaux enregistrés dans `settings`, lus par `config/theme.php` |
| E2 | **Export CSV** (ventes, abonnés) | ✅ | 1 h | ✅ abonnés newsletter (`admin/export_subscribers.php`) ; ✅ ventes (`admin/export_purchases.php`) avec filtres et protection Excel |
| E3 | **Journal des actions** | ✅ | 1 jour | `admin/activity_log.php` : qui a créé, modifié, supprimé, envoyé, changé un réglage ; table `admin_activity_log` + helper `admin_activity()` |
| E4 | **Sauvegardes automatiques** | ⏳ | serveur | à configurer chez OVH |
| E5 | **Versions des articles** | ⏳ | 1 jour | revenir à une ancienne version |

---

## F. Contenus provisoires à remplacer (client)

| # | Contenu | État | Où |
|---|---|---|---|
| F1 | Téléphone `+229 XX XX XX XX` | ⏳ | valeur à saisir dans **Admin → Réglages du site** (plus aucune valeur en dur) |
| F2 | 4 icônes sociales vers `#` | ✅ | générées depuis les réglages ; un réseau vide n'apparaît plus |
| F3 | Adresses des réseaux à confirmer | ⏳ | à saisir dans **Admin → Réglages du site** |
| F4 | FAQ de la page contact | ⏸ retirée volontairement | composant conservé : `includes/faq-section.php` |

---

## G. Dépend du serveur ou du client (pas du code)

| # | Point | État | Bloqué par |
|---|---|---|---|
| G1 | Clés du prestataire de paiement (Kkiapay ou FedaPay) | ⏸ | clés **sandbox** disponibles après inscription gratuite ; clés réelles après validation KYC |
| G2 | Domaine + hébergement OVH | ⏸ | achat client |
| G3 | SSL (HTTPS) | ⏸ | hébergement |
| G4 | SMTP + SPF/DKIM | ⏸ | hébergement / boîte mail |
| G5 | `composer install` | ⏸ | serveur |
| G6 | Migrations paiement + newsletter | ✅ | incluses dans `sql/export_production.sql` et appliquées par `dev-start.sh` |
| G7 | Changement du mot de passe admin | ⏸ | à faire juste après la mise en ligne |

---

## H. Tableau de bord admin plus professionnel ✅ Fait (6 nov. 2026)

- **Objectif :** donner une vue de pilotage, pas seulement des compteurs.
- **Réalisé :**
  - indicateurs métier : **revenus du mois**, ventes payées, abonnés newsletter, documents payants ;
  - comparaison avec le mois précédent ;
  - bloc **« À traiter »** : commentaires à valider, brouillons, articles programmés ;
  - bloc **« Dernières ventes »** avec acheteur, document, montant et date ;
  - listes : derniers articles, commentaires récents, articles les plus lus ;
  - statuts traduits en français (Publié / Brouillon / Programmé) ;
  - mise en page cohérente, responsive, sans débordement sur téléphone ;
  - un **auteur** ne voit que ses propres contenus (correction de confidentialité).
- **Commit :** `c1043a3`

---

## H. Newsletter : envoi réel, désinscription et export ✅ Fait (6 nov. 2026)

- **Objectif :** envoyer une lettre aux abonnés depuis l'administration, avec un lien de désinscription fiable.
- **Réalisé :**
  - inscription avec **jeton unique** de désinscription ; réinscription qui réactive sans créer de doublon ;
  - écran **Newsletter** : total, actifs, désinscrits, filtres, recherche, pagination, désinscrire/réactiver ;
  - **export CSV** des abonnés (UTF-8 avec BOM, séparateur `;`, lisible dans Excel) ;
  - écran **Écrire une newsletter** : sujet, message texte, **aperçu**, envoi réel ;
  - **historique des envois** dans `newsletter_campaigns` (destinataires, envoyés, échecs) ;
  - page publique `public/desinscription.php` : confirmation obligatoire, adresse masquée, non indexée ;
  - lien de désinscription automatique dans chaque e-mail ;
  - erreurs honnêtes si le SMTP n'est pas configuré.
- **Migration :** `sql/migration_newsletter_suite.sql`
- **Commit :** `470f596`

---

## Décisions à prendre

| Sujet | Options | Décision | Date |
|---|---|---|---|
| Comptes visiteurs | Masquer définitivement / garder pour plus tard | **On garde** : futurs professeurs contributeurs (B6) | 6 nov. 2026 |
| Cours payants | Lien privé sans compte / espace « Mes cours » | **Lien privé sans compte** (e-mail + jeton) | 6 nov. 2026 |
| Prestataire de paiement | Kkiapay / FedaPay / autre | _en comparaison_ | |
| Vidéos | Liens signés / service privé | Vidéos d'articles : `video.php` ; cours : `course_file.php` avec contrôle d'achat | 6 nov. 2026 |

---

## Historique des intégrations

| Date | Fonctionnalité | Commit | Vérifié | Notes |
|---|---|---|---|---|
| 6 nov. 2026 | Paiement indépendant du prestataire (couche neutre) | `8b5933c` | oui | connecteurs Kkiapay + FedaPay |
| 6 nov. 2026 | Protection des documents et des vidéos | `c1043a3` | oui (local), à revérifier sur Apache | `.htaccess` + `video.php` |
| 6 nov. 2026 | Message d'erreur honnête sur le contact | `c1043a3` | oui | plus de faux « message envoyé » |
| 6 nov. 2026 | Tableau de bord admin professionnel | `c1043a3` | oui | revenus, ventes, à traiter, responsive |
| 6 nov. 2026 | Envoi automatique du lien d'achat par e-mail (B1) | `b61c8b3` | oui (contenu), envoi réel : SMTP | `build_purchase_email()` + `payment_callback.php` |
| 6 nov. 2026 | Renvoi du lien + recherche dans Ventes (B2) | `b61c8b3` | oui (interface), envoi réel : SMTP | `admin/manage_purchases.php` |
| 6 nov. 2026 | Page « Retrouver mon achat » (B3) | `93209d4` | oui (local), envoi réel : SMTP | `public/retrouver-mon-achat.php`, limite anti-abus |
| 6 nov. 2026 | Newsletter : envoi, désinscription, écran admin, export CSV (D1–D3) | `470f596` | oui (local), envoi réel : SMTP | `send_newsletter.php`, `desinscription.php`, `manage_newsletter.php` |
| 7 nov. 2026 | Mot de passe oublié pour l'administration (B5) | `797f222` | oui (local), envoi réel : SMTP | jeton haché, valable 1 h, usage unique, limite par IP |
| 7 nov. 2026 | Export CSV des ventes (E2) | `797f222` | oui (local) | filtres respectés, protection contre les formules Excel |
| 7 nov. 2026 | Réglages du site (E1) | `e1f17be` | oui (local) | coordonnées et réseaux dans `settings`, page Contact branchée, réseaux vides masqués |
| 7 nov. 2026 | Journal des actions admin (E3) | `e1f17be` | oui (local) | connexion, créations, modifications, suppressions, envois, réglages ; filtres et pagination |
| | | | | |
