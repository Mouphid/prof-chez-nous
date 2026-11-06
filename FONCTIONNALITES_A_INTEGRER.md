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

### A1. Protéger les fichiers de `uploads/` ⏳
- **Constat :** tous les fichiers sont accessibles par une adresse directe. Test effectué :
  `http://127.0.0.1:8080/uploads/pdf/article 4.pdf` → **HTTP 200, 5,7 Mo téléchargés**.
  Cela contourne complètement le paiement.
- **À faire :** empêcher l'accès direct aux dossiers `uploads/pdf`, `uploads/docs`, `uploads/video`
  (règle serveur `.htaccess` + contrôle dans `download.php`), et garder `uploads/images` public.
- **Fichiers :** `.htaccess`, `public/download.php`, `uploads/`
- **Durée :** 30 min à 1 h
- **Dépend de :** rien

### A2. Message honnête si l'e-mail de contact échoue ⏳
- **Constat :** `public/contact.php` affiche « Votre message a été envoyé avec succès » même quand l'envoi échoue.
- **À faire :** afficher une erreur claire (« l'envoi a échoué, écrivez-nous à contact@… ») et journaliser.
- **Fichiers :** `public/contact.php`, `config/mail.php`
- **Durée :** 15 min
- **Dépend de :** rien

---

## B. Priorité 2 — Achats sans compte visiteur

> Rappel : **aucun compte visiteur**. L'acheteur paie, le serveur vérifie, un lien privé est généré.

### B1. Envoyer le lien d'achat par e-mail ⏳
- **Constat :** après paiement, le lien n'existe que dans la redirection. Si l'acheteur ferme la page, il perd l'accès.
- **À faire :** envoyer automatiquement le lien de téléchargement à l'adresse saisie, avec le nom du document et le montant.
- **Fichiers :** `public/payment_callback.php`, `config/mail.php`
- **Durée :** 1 à 2 h
- **Dépend de :** configuration SMTP (voir G4)

### B2. Renvoyer un lien depuis l'admin ⏳
- **Constat :** l'écran Ventes affiche les achats mais ne peut pas renvoyer l'accès.
- **À faire :** bouton « Renvoyer le lien » + recherche par e-mail dans `admin/manage_purchases.php`.
- **Durée :** 1 h
- **Dépend de :** B1

### B3. Page « Retrouver mon achat » ⏳ (optionnel)
- **À faire :** formulaire e-mail → renvoi du lien. Évite les demandes manuelles.
- **Durée :** 1 h
- **Dépend de :** B1

### B4. Masquer Inscription / Connexion ⏸
- **Constat :** le menu affiche encore « Inscription » et « Connexion » (`includes/navbar.php`),
  alors que l'option retenue est **sans compte visiteur**. Les pages `register.php`, `login.php`,
  `profile.php`, `my_downloads.php`, `verify-email.php` existent toujours.
  Base actuelle : **1 seul compte (admin)**.
- **Décision à prendre :** masquer les liens et désactiver l'inscription, ou assumer les comptes.
- **Durée :** 30 min
- **Dépend de :** ta décision

### B5. Mot de passe oublié (admin) ⏳
- **Constat :** aucune page de récupération. Si le mot de passe admin est perdu, l'accès est bloqué.
- **À faire :** lien de réinitialisation par e-mail.
- **Durée :** 1 à 2 h
- **Dépend de :** SMTP

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

### C3. Protéger les vidéos ⏳
- **Constat :** `post.php` sert la vidéo par une adresse directe (`../uploads/video/...`).
- **À faire :** liens signés à durée limitée **ou** service de vidéo privée (Vimeo privé, Bunny Stream).
- **Durée :** 1 jour (liens signés) ou abonnement externe
- **Dépend de :** A1

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

## Décisions à prendre

| Sujet | Options | Décision | Date |
|---|---|---|---|
| Comptes visiteurs | Masquer définitivement / garder pour plus tard | _à décider_ | |
| Cours payants | Lien privé sans compte / espace « Mes cours » | _à décider_ | |
| Prestataire de paiement | Kkiapay / FedaPay / autre | _en comparaison_ | |
| Vidéos | Liens signés / service privé | _à décider_ | |

---

## Historique des intégrations

| Date | Fonctionnalité | Commit | Vérifié | Notes |
|---|---|---|---|---|
| 6 nov. 2026 | Paiement indépendant du prestataire (couche neutre) | `8b5933c` | oui | connecteurs Kkiapay + FedaPay |
| | | | | |
| | | | | |
