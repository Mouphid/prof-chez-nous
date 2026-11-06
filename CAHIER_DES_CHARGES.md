# 📐 CAHIER DES CHARGES — Site « Joie Enseignante »

**Version :** 1.0 — **Date :** 28 septembre 2026
**Maître d'ouvrage (Client) :** M. Sylvestre Djouamon — Professeur de Littérature, Université de Cotonou
**Maître d'œuvre (Prestataire) :** Adonis — Développeur web indépendant
**Projet :** Conception, développement et mise en ligne du site vitrine-blog « Joie Enseignante » avec abonnement hébergement de 2 ans

---

## 1. CONTEXTE ET OBJECTIFS

### 1.1 Contexte
M. Sylvestre Djouamon, enseignant-chercheur en littérature, souhaite disposer d'un site personnel professionnel pour :
- Présenter sa biographie et son parcours académique ;
- Publier ses recherches, articles scientifiques et supports de cours ;
- Donner accès à ses publications au format PDF ;
- Interagir avec étudiants et collègues (commentaires, likes) ;
- Affirmer une présence en ligne crédible sous une marque : **« Joie Enseignante »**.

### 1.2 Objectifs
1. **Institutionnel** : asseoir l'image du professeur (bio, parcours, publications) ;
2. **Pédagogique** : diffuser cours et ressources aux étudiants ;
3. **Scientifique** : porter à connaissance les articles et publications de recherche ;
4. **Relationnel** : créer une communauté (abonnement newsletter, commentaires).

### 1.3 Cible
Étudiants, collègues enseignants, chercheurs, grand public francophone (Bénin, Afrique de l'Ouest, diaspora).

---

## 2. CHOIX STRATÉGIQUES VALIDÉS PAR LE CLIENT

| Sujet | Décision |
|---|---|
| Nom de domaine | **`joiedenseignante.com`** (`.com` — car `joieenseignante.com` est pris) |
| Durée d'engagement | **2 ans** (hébergement + domaine + mise en ligne) |
| Budget | **94 250 FCFA TTC** (devis n° JE-2026-002 signé) |
| Nom de marque | **Joie Enseignante** |

---

## 3. IDENTITÉ VISUELLE ET ERGONOMIE

### 3.1 Principes
- Design **épuré et professionnel**, tons **bleu nuit / indigo (primary)** rehaussés d'un accent **ambre/doré (accent)**, héritier des codes d'une institution académique ;
- **Glassmorphism** modéré : barres de navigation et bannières translucides (blur) ;
- **Mode sombre complet** (toggle mémorisé, respect des préférences système) ;
- Typographie **Inter** (auto-hébergée), hiérarchie claire, gouttières généreuses ;
- Icônes **Phosphor** (style cohérent, rendu font).

### 3.2 Responsive
- Mobile-first : navigation hamburger, grilles qui se replient (sm/lg), hero adaptatif ;
- Testé aux formats smartphone, tablette, ordinateur.

### 3.3 Accessibilité (WCAG)
- Langue `fr`, skip-link, aria-labels, labels de formulaires ;
- Contrastes adaptés aux thèmes clair/sombre ; navigation clavier possible ;
- Images porteuses d'information avec `alt` descriptif.

---

## 4. PÉRIMÈTRE FONCTIONNEL — SITE PUBLIC

| # | Fonction | Description | Statut |
|---|---|---|---|
| F1 | Accueil | Hero slider (3 visuels, 6 s), section « Bienvenue », 6 cartes d'atouts, derniers articles, newsletter | ✅ |
| F2 | Articles | Liste paginée, filtres par catégorie, lecture complète, compteur de vues | ✅ |
| F3 | Cours & Publications | Listes de ressources avec fichiers téléchargeables (PDF) | ✅* |
| F4 | Biographie | Présentation du professeur, parcours | ✅ |
| F5 | Contact | Formulaire sécurisé (CSRF + email), infos de contact, FAQ | ✅ |
| F6 | Recherche | Recherche plein texte sur les articles | ✅ |
| F7 | Espace membre | Inscription, connexion, profil, mes commentaires, mes téléchargements | ⚠️ Désactivé (voir §8) |
| F8 | Tri/archives | Pagination, catégories | ✅ |
| F9 | Pages légales | Mentions légales, confidentialité, conditions | ✅ |

*\* F3 dépend de la réactivation de `download.php` (voir §8).*

---

## 5. PÉRIMÈTRE FONCTIONNEL — ESPACE ADMIN

| # | Fonction | Description |
|---|---|---|
| A1 | Connexion sécurisée | CSRF + limitation de tentatives + bcrypt + régénération de session |
| A2 | Dashboard | Statistiques : articles, brouillons, programmés, commentaires (attente), vues, likes, catégories, activité 30 j |
| A3 | Articles | Créer, modifier, supprimer ; statut (brouillon/publié/programmé/archivé) ; image à la une, vidéo, fichiers joints |
| A4 | Catégories | Ajouter, renommer, supprimer, affecter aux articles |
| A5 | Commentaires | Valider, masquer, supprimer (modération) |
| A6 | Utilisateurs | Lister, attribuer un rôle, activer/désactiver |
| A7 | Rôles | `admin` (tout), `auteur` (articles+commentaires), `etudiant` (téléchargements) |
| A8 | Journal | Logs des connexions et actions sensibles (`logs/app.log`) |

---

## 6. TECHNOLOGIES ET ARCHITECTURE

- **Langages :** PHP 8.3 (sans framework), MySQL 8, HTML5/CSS3, JavaScript vanilla, AJAX
- **Base de données :** 9 tables — `users`, `categories`, `posts`, `files`, `comments`, `likes`, `settings`, `article_reads`, `user_downloads`
- **Serveur :** Apache (fichier `.htaccess` de sécurité), compatible PHP 8.3 + MySQL 8 sur **hébergement Pro OVHcloud**
- **Sécurité :** requêtes préparées (PDO), échappement XSS, tokens CSRF, mots de passe bcrypt, validation MIME réelle des uploads, en-têtes HTTP de sécurité, blocage des dossiers sensibles
- **Stockage :** fichiers dans `uploads/` (images, pdf, docs)
- **Mail :** PHPMailer (SMTP) — formulaire de contact et confirmations
- **Configuration** par variables d'environnement (pas de mots de passe en clair dans le code)

---

## 7. EXIGENCES DE QUALITÉ

### 7.1 Sécurité (critères acceptation)
- Toutes requêtes SQL préparées ; aucune donnée utilisateur échappée avant affichage ;
- Uploads limités aux extensions et MIME autorisés, PHP non exécutable dans `uploads/` ;
- Tokens CSRF sur toute action modifiant l'état ;
- Mot de passe admin haché (bcrypt) ;
- Absence d'informations de connexion dans les fichiers publics ;
- Accès `config/`, `includes/`, `sql/`, `logs/`, `vendor/` bloqués (403).

### 7.2 Performance
- Chargement < 2-3 s sur connexion mobile moyenne ;
- Images optimisées (compression) ;
- Lazy-loading des images hors du premier écran.

### 7.3 Compatibilité
- Dernières versions de Chrome, Firefox, Safari, Edge + navigateurs mobiles ;
- Affichage correct en clair et sombre.

### 7.4 Disponibilité
- Hébergement mutualisé Pro OVHcloud (garantie du fournisseur) ;
- Sauvegardes : celles incluses par l'hébergeur + sauvegarde manuelle périodique par le prestataire (pendant l'abonnement).

---

## 8. 🚩 POINTS À TRANCHER PAR LE MAÎTRE D'OUVRAGE (avant mise en ligne)

> Issue de l'audit (28 sept 2026). Les fonctionnalités suivantes existent dans le code mais sont **intentionnellement désactivées** (redirection vers l'accueil) :

| Fonction | Question au Client | Option recommandée |
|---|---|---|
| Inscription visiteurs | Le public doit-il créer un compte ? | **Non** — site vitrine géré par l'admin seul ✔ |
| Profil / Mes commentaires / Mes téléchargements (espace membre) | Garder un espace membre ? | **Retirer** du menu (site 1 auteur) ✔ |
| Téléchargement PDF public | Les visiteurs peuvent-ils télécharger les PDF ? | **Oui** — réactiver `download.php` ✔ |

**Décision à valider (cocher d'une croix) :**
- [x] Le site est **géré exclusivement par l'administrateur** (professeur) : désactivation des comptes visiteurs maintenue, les publications sont téléchargeables librement. **← option retenue le 5 novembre 2026**
- [ ] Le site **autorise les comptes visiteurs** (inscription + connexion) : réactivation des fonctionnalités membre.
- [ ] Le site autorise les comptes visiteurs **sans téléchargement public** (accès réservé aux connectés).

### Complément validé : document gratuit ou payant

Chaque document joint à un article peut être marqué :
- **gratuit** (prix = 0) → téléchargement direct, sans compte ;
- **payant** (prix > 0) → page d'achat avec paiement en ligne (Kkiapay), puis téléchargement automatique après confirmation.

> Cette option est indépendante du choix ci-dessus et déjà implémentée dans l'administration.

> ⚠️ Conséquence : la réponse détermine la configuration finale de `download.php`, `register.php`, `my_comments.php`, `my_downloads.php`, `profile.php` et la présence du bouton « Connexion/Inscription » dans la barre de navigation.

---

## 9. CONTENU INITIAL (fourni par le client — déjà intégré)

- **Identification :** logo, photos, bannières (déjà en place)
- **Biographie :** « Biographie de Sylvestre Djouamon » (fichier Word fourni, synthetisé en page)
- **Publications :** ~18 articles scientifiques en PDF (articles IJCR, recherches sur la littérature orale fon, chansons traditionnelles, syncrétisme, droits de l'enfant, esthétique et parémie fon, etc.)
- **Articles de blog :** 21 articles déjà intégrés
- **Catégories :** pré-remplies (4 à 6 catégories)

---

## 10. LIVRABLES ET DÉLAIS

### 10.1 Livrables
1. Site complet fonctionnel et configuré sur le nom de domaine `joiedenseignante.com` ;
2. Espace d'administration accessible ;
3. Documents contractuels : devis signé, facture pro-forma, fiche d'infos juriste ;
4. Manuel d'administration court (créer un article, publier, gérer les fichiers) ;
5. Accès et identifiants de gestion (hébergement OVHcloud + domaine + admin du site).

### 10.2 Planning indicatif
| Étape | Délai |
|---|---|
| Validation du présent cahier des charges | J0 |
| Préparation et mise en ligne (corrections audit + déploiement) | J+3 à J+5 |
| Recette client (validation site en ligne) | J+5 à J+10 |
| Mise en service définitive | J+10 |

### 10.3 Modalités de recette
Le client valide page par page (accueil, articles, cours, publications, biographie, contact, admin) sur le site en ligne avant mise en service définitive.

---

## 11. MAINTENANCE ET ENGAGEMENT (2 ans)

Prestations incluses pendant 24 mois (incluses dans le forfait) :
- Hébergement et nom de domaine maintenus actifs (renouvellements gérés par le prestataire pendant la durée) ;
- Mises à jour de sécurité ;
- Sauvegarde et restauration en cas d'incident ;
- Assistance technique (dépannage, aide à la publication) par canal convenu ;
- Supervision de la disponibilité du site.

**Exclus :** création de nouveaux contenus, refonte, développement de nouvelles fonctionnalités (devis séparé).

### 11.1 Renouvellement (année 3)
Tarif alors en vigueur, estimé à **~71 750 FCFA/an** (≈ 62 900 F hébergement + 8 850 F domaine) — à confirmer 60 jours avant l'échéance.

---

## 12. ASPECTS JURIDIQUES ET CONFIDENTIALITÉ

- **Propriété intellectuelle** : les contenus publiés appartiennent au Client ; le code de la plateforme reste la propriété du Prestataire (licence d'utilisation pendant la durée, sauf acquisition séparée) ; le nom de domaine est enregistré au nom du Client.
- **Données personnelles** : collecte minimale (nom, email), finalité (commentaires, formulaire de contact), accès réservé, conformité à la loi béninoise sur les données à caractère personnel ; politique de confidentialité en place.
- **Confidentialité** : le prestataire ne divulgue ni accès, ni contenus, ni données.
- **Droit applicable** : République du Bénin ; tentative de règlement amiable préalable obligatoire.

---

## 13. VALIDATION

Le présent cahier des charges est accepté par :

**Le Maître d'ouvrage (Client)**                     **Le Maître d'œuvre (Prestataire)**
Nom : M. Sylvestre Djouamon                           Nom : Adonis
Signature : ____________________________              Signature : ____________________________
Date : ____ / ____ / 2026                             Date : ____ / ____ / 2026

---

*Annexes : Audit technique (AUDIT_TECHNIQUE.md), Devis n° JE-2026-002, Facture pro-forma n° PF-2026-001, Fiche d'informations juriste.*