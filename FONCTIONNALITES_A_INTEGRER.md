# 📌 Suivi des fonctionnalités à intégrer

**Projet :** Joie Enseignante — `joiedenseignante.com`
**Créé le :** 6 novembre 2026
**Dernière mise à jour :** 6 novembre 2026

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

### B3. Page « Retrouver mon achat » ⏳ (optionnel)
- **À faire :** formulaire e-mail → renvoi du lien. Évite les demandes manuelles.
- **Durée :** 1 h
- **Dépend de :** B1

### B4. Inscription / Connexion : **on garde** ✅ Décision du 6 nov. 2026
- **Décision :** ne pas masquer. Les comptes vont servir plus tard aux **contributeurs
  professionnels** (autres professeurs) : voir B6.
- **Déjà corrigé :** le lien de vérification d'e-mail ne contient plus de chemin en dur
  (`/JoieEnseignante/…`) ; il utilise `SITE_URL`.
- **Fichiers :** `public/register.php`, `public/resend-verification.php`

### B5. Mot de passe oublié (admin) ⏳
- **Constat :** aucune page de récupération. Si le mot de passe admin est perdu, l'accès est bloqué.
- **À faire :** lien de réinitialisation par e-mail.
- **Durée :** 1 à 2 h
- **Dépend de :** SMTP

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

> Décision de principe : **sans compte**, lien privé après paiement (à confirmer).

### C1. Table `courses` + écran d'administration ⏳
- **Constat :** `public/cours.php` contient 3 cours écrits en dur. Aucune table, aucun prix, aucun fichier rattaché.
- **À faire :** table `courses` (titre, description, chapitres, PDF/vidéo, prix, gratuit/payant),
  écran admin **Cours** (créer / modifier / supprimer).
- **Durée :** 1 à 2 jours

### C2. Table `course_purchases` + accès par jeton ⏳
- **À faire :** réutiliser le modèle de `purchases` : statut payé, jeton d'accès, contrôle anti-rejeu.
- **Durée :** 1 jour
- **Dépend de :** C1

### C3. Protéger les vidéos ✅ Fait pour les vidéos d'articles (6 nov. 2026)
- **Correction :** `uploads/video` n'est plus accessible directement ; la lecture passe par
  `public/video.php`, qui vérifie que la vidéo appartient à un article visible.
- **Reste à faire :** pour les **vidéos de cours payants**, ajouter le contrôle du jeton d'achat
  (même logique que `download.php`). À traiter avec C2.
- **Commit :** `c1043a3`

### C4. Espace « Mes cours » ⏸ (seulement si nécessaire plus tard)
- **À faire :** comptes visiteurs + historique d'achats.
- **Décision :** uniquement si le volume de clients le justifie.

---

## D. Newsletter

### D1. Envoi réel des e-mails ⏳
- **Constat :** `newsletter_subscribe.php` enregistre seulement l'adresse. Aucun envoi, aucun contenu.
- **Durée :** 1/2 journée
- **Dépend de :** SMTP

### D2. Lien de désinscription ⏳
- **À faire :** jeton de désinscription dans chaque e-mail + page de confirmation.
- **Durée :** 2 h
- **Dépend de :** D1

### D3. Écran admin + export ⏳
- **À faire :** liste des abonnés, nombre, export CSV.
- **Durée :** 2 h

---

## E. Confort d'administration

| # | Fonctionnalité | État | Durée | Détail |
|---|---|---|---|---|
| E1 | Écran **Réglages du site** | ⏳ | 1/2 journée | téléphone, adresse, réseaux sociaux sont écrits dans `config/theme.php` |
| E2 | **Export CSV** (ventes, abonnés) | ⏳ | 1 h | aucun export actuellement |
| E3 | **Journal des actions** | ⏳ | 1 jour | savoir qui a modifié quoi |
| E4 | **Sauvegardes automatiques** | ⏳ | serveur | à configurer chez OVH |
| E5 | **Versions des articles** | ⏳ | 1 jour | revenir à une ancienne version |

---

## F. Contenus provisoires à remplacer (client)

| # | Contenu | État | Où |
|---|---|---|---|
| F1 | Téléphone `+229 XX XX XX XX` | ⏳ | `config/theme.php`, `public/contact.php`, `includes/contact-section.php` |
| F2 | 4 icônes sociales vers `#` | ⏳ | `public/contact.php` (lignes 138-141), `includes/contact-section.php` |
| F3 | Adresses des réseaux à confirmer | ⏳ | `config/theme.php` (`SOCIAL_*`) |
| F4 | FAQ de la page contact | ⏸ retirée volontairement | composant conservé : `includes/faq-section.php` |

---

## G. Dépend du serveur ou du client (pas du code)

| # | Point | État | Bloqué par |
|---|---|---|---|
| G1 | Clés du prestataire de paiement (Kkiapay ou FedaPay) | ⏸ | validation du compte marchand |
| G2 | Domaine + hébergement OVH | ⏸ | achat client |
| G3 | SSL (HTTPS) | ⏸ | hébergement |
| G4 | SMTP + SPF/DKIM | ⏸ | hébergement / boîte mail |
| G5 | `composer install` | ⏸ | serveur |
| G6 | Migrations paiement + newsletter | ⏸ | base de production |
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

## Décisions à prendre

| Sujet | Options | Décision | Date |
|---|---|---|---|
| Comptes visiteurs | Masquer définitivement / garder pour plus tard | **On garde** : futurs professeurs contributeurs (B6) | 6 nov. 2026 |
| Cours payants | Lien privé sans compte / espace « Mes cours » | _à décider_ | |
| Prestataire de paiement | Kkiapay / FedaPay / autre | _en comparaison_ | |
| Vidéos | Liens signés / service privé | Vidéos d'articles protégées par `video.php` ; reste le jeton pour les cours payants | 6 nov. 2026 |

---

## Historique des intégrations

| Date | Fonctionnalité | Commit | Vérifié | Notes |
|---|---|---|---|---|
| 6 nov. 2026 | Paiement indépendant du prestataire (couche neutre) | `8b5933c` | oui | connecteurs Kkiapay + FedaPay |
| 6 nov. 2026 | Protection des documents et des vidéos | `c1043a3` | oui (local), à revérifier sur Apache | `.htaccess` + `video.php` |
| 6 nov. 2026 | Message d'erreur honnête sur le contact | `c1043a3` | oui | plus de faux « message envoyé » |
| 6 nov. 2026 | Tableau de bord admin professionnel | `c1043a3` | oui | revenus, ventes, à traiter, responsive |
| 6 nov. 2026 | Envoi automatique du lien d'achat par e-mail (B1) | à venir | oui (contenu), envoi réel : SMTP | `build_purchase_email()` + `payment_callback.php` |
| 6 nov. 2026 | Renvoi du lien + recherche dans Ventes (B2) | à venir | oui (interface), envoi réel : SMTP | `admin/manage_purchases.php` |
| | | | | |
| | | | | |
