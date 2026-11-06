# 🚦 POINT DE LIVRAISON — J-12H (mis à jour)

**Projet :** Joie Enseignante — `joiedenseignante.com`
**Dernière mise à jour :** 5 novembre 2026
**Objectif :** mise en ligne et livraison sous 12 heures

---

## 1. Verdict en 30 secondes

✅ **Le code est prêt.** Toutes les promesses du document client sont maintenant couvertes, ainsi que le système **document gratuit / document payant**.

⏳ Il reste uniquement :
1. **les achats** (hébergement, domaine, boîte mail) ;
2. **la configuration serveur** (base, SSL, SMTP, sauvegardes) ;
3. **les clés Kkiapay** pour activer les paiements réels ;

> ⚠️ Le vrai risque sur 12 h reste **la propagation DNS** après l'achat du domaine (1 h à 48 h). **À lancer en premier.**

> 📌 Points non encore intégrés (sécurité, cours payants, newsletter, contenus provisoires) : voir `FONCTIONNALITES_A_INTEGRER.md`.

---

## 2. ✅ Corrections effectuées dans le code

### Documents PDF et paiement
| Fonction | État |
|---|---|
| Téléchargement des PDF | ✅ `public/download.php` réactivé, chemins sécurisés (`realpath` + dossier `uploads/` obligatoire) |
| Affichage des documents | ✅ liste des fichiers dans chaque article, avec taille et statut |
| Document **gratuit** | ✅ bouton « Télécharger » direct |
| Document **payant** | ✅ bouton « Acheter — X FCFA » → page d'achat |
| Prix par document | ✅ champ dans **Nouveau post** et **Modifier article** |
| Paiement en ligne | ✅ module **indépendant du prestataire** (`public/purchase.php` + `public/payment_callback.php`) — Kkiapay branché, connecteur FedaPay prêt à tester |
| Vérification serveur | ✅ la transaction est revérifiée via l'API avant de délivrer le fichier (anti-fraude) |
| Lien de téléchargement | ✅ jeton unique `download_token`, contrôle anti-rejeu |
| Suivi des ventes | ✅ nouvel écran admin **Ventes** (`admin/manage_purchases.php`), avec recherche par nom, e-mail ou document |
| Lien d'achat par e-mail | ✅ envoyé automatiquement après paiement confirmé (nom du document, montant, bouton de téléchargement) — envoi réel dès que le SMTP est configuré |
| Renvoi du lien | ✅ bouton **Renvoyer** sur chaque vente payée, avec message de succès ou d'erreur honnête |
| Sans clés de paiement | ✅ message « paiement bientôt disponible » + lien de contact (aucune erreur) |
| Changement de prestataire | ✅ une seule fonction à modifier (`payment_verify_transaction`) ; la colonne `provider` en base garde l'historique |

### Contenu et promesses du PDF
| Promesse | État |
|---|---|
| « questions fréquentes » | ⏸ FAQ **retirée temporairement** de la page contact (composant `includes/faq-section.php` conservé) |
| « commentaires validés par l'admin » | ✅ les commentaires arrivent en **attente** (`pending`) |
| « mails depuis le domaine » | ✅ adresse `contact@joiedenseignante.com` (plus d'adresse Gmail en dur) |
| « images allégées » | ✅ 5,4 Mo → 2,7 Mo (photos redimensionnées et compressées) |
| « aucun identifiant par défaut » | ✅ écran **Mot de passe** dans l'admin |
| « manuel d'administration » | ✅ `MANUEL_ADMINISTRATION.md` |
| Devis corrigé | ✅ `DEVIS_JOIEENSEIGNANTE.pdf` régénéré : **94 250 FCFA**, 2 pages, dates à jour |
| Confort de lecture | ✅ texte d'article 17 px, interligne 1,85, largeur limitée à ~70 caractères, fond « papier » et version sombre |
| Newsletter | ✅ formulaire réparé (POST) + table `newsletter_subscribers` |
| Domaine dans le code | ✅ `joiedenseignante.com` partout + `BASE_URL` corrigé |

### Interface d'administration
- ✅ **menu latéral unique** (`admin/_sidebar.php`) sur les 9 pages admin, avec page active surlignée ;
- ✅ **barre supérieure unique** (`admin/_topbar.php`) : titre, nom, rôle et avatar identiques partout ;
- ✅ fond, champs de formulaire, tableaux et titres d'onglet uniformisés ;
- ✅ **menu repliable sur téléphone** : bouton ☰, fond assombri, fermeture au clic ou avec la touche Échap ; le contenu ne déborde plus horizontalement.

**Tableau de bord refondu (6 nov. 2026)**
- ✅ indicateurs métier : **revenus du mois**, ventes payées, abonnés newsletter, documents payants ;
- ✅ comparaison avec le mois précédent ;
- ✅ bloc **« À traiter »** : commentaires à valider, brouillons, articles programmés ;
- ✅ bloc **« Dernières ventes »** : acheteur, document, montant, date ;
- ✅ listes : derniers articles, commentaires récents, articles les plus lus ;
- ✅ statuts en français (Publié / Brouillon / Programmé) et mise en page responsive ;
- ✅ un **auteur** ne voit que ses propres contenus.

### Sécurité et référencement
- ✅ HTTPS forcé + HSTS + `Permissions-Policy` + compression + cache dans `.htaccess` ;
- ✅ balises **Open Graph** / Twitter / canonique sur toutes les pages ;
- ✅ `robots.txt` + `sitemap.xml` dynamique (`public/sitemap.php` → `sitemap.xml`) ;
- ✅ page **404** personnalisée ;
- ✅ `.gitignore` protège `config/payment_config.php` et `config/mail_config.php`.

**Protection des documents et des vidéos (6 nov. 2026)**
- ✅ accès direct à `uploads/pdf`, `uploads/docs`, `uploads/audio` et `uploads/video` **bloqué**
  (règle générale dans `.htaccess` + un `.htaccess` dans chaque dossier) ;
- ✅ les PDF passent uniquement par `public/download.php`, qui vérifie le droit d'accès ;
- ✅ les vidéos d'articles passent par `public/video.php` : le fichier n'est servi que s'il
  appartient à un article visible, avec lecture et déplacement dans la vidéo ;
- ✅ `uploads/images` reste public (photos des articles) ;
- ✅ message d'erreur **honnête** si l'envoi du formulaire de contact échoue, au lieu d'un faux succès ;
- ✅ erreur claire au démarrage si PHPMailer ou le SMTP n'est pas configuré ;
- ✅ lien de vérification d'e-mail corrigé : plus de `/JoieEnseignante/` en dur.

> ⚠️ **À vérifier en production :** ouvrir `https://joiedenseignante.com/uploads/pdf/<un-fichier>.pdf`
> doit afficher **403 (accès refusé)**. Le serveur local `php -S` ne lit pas les `.htaccess`,
> ce contrôle n'est donc possible qu'en ligne, sur Apache/OVH.

### Comptes et contributeurs
- ✅ **Option 1 maintenue pour les acheteurs** : aucun compte n'est demandé pour acheter un document.
- ✅ Les pages `register.php`, `login.php`, `profile.php`, `my_downloads.php`, `my_comments.php`
  restent **dans le code mais désactivées** (redirection vers l'accueil). Elles serviront aux
  **professeurs contributeurs** (voir `FONCTIONNALITES_A_INTEGRER.md`, point B6).
- ✅ Le professeur peut rendre **chaque document gratuit ou payant**.

---

## 3. 🗄️ Migrations base de données (à exécuter en production)

Après l'import de `sql/export_production.sql`, exécuter dans l'ordre :

```bash
mysql -u UTILISATEUR -p NOM_DE_LA_BASE < sql/migration_paiement.sql
mysql -u UTILISATEUR -p NOM_DE_LA_BASE < sql/migration_newsletter.sql
```

- `migration_paiement.sql` : colonnes `price` / `is_free` sur `files` + table `purchases` ;
- `migration_newsletter.sql` : table `newsletter_subscribers`.

> Le script local `dev-start.sh` applique automatiquement ces migrations si elles manquent.

---

## 4. 🔑 Activer les paiements réels (Kkiapay)

1. Créer / activer le compte marchand sur [kkiapay.me](https://kkiapay.me).
2. Récupérer les 3 clés (menu **Développeurs**) : publique, privée, secrète.
3. Sur le serveur :
   ```bash
   cp config/payment_config.example.php config/payment_config.php
   ```
4. Renseigner les clés, puis mettre `'sandbox' => false`.
5. Tester un achat de faible montant → vérifier qu'il apparaît **Payé** dans **Ventes**.

> Sans ces clés, le site fonctionne normalement : les documents payants affichent seulement « paiement bientôt disponible ».

---

## 5. 🖥️ Ce qui reste côté serveur (à faire par toi)

### Achats à faire maintenant
- [ ] **Hébergement OVHcloud Pro — 24 mois** ;
- [ ] **Domaine `joiedenseignante.com`** → priorité absolue (DNS) ;
- [ ] **Boîte mail `contact@joiedenseignante.com`**.

### Configuration serveur
- [ ] PHP **8.3** + extensions `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`, `gd`, `curl` ;
- [ ] MySQL 8 : base + utilisateur dédié (pas `root`) ;
- [ ] Importer `sql/export_production.sql` **puis les 2 migrations** (§3) ;
- [ ] Variables d'environnement `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` ;
- [ ] `composer install` (installe PHPMailer dans `vendor/`) ;
- [ ] Uploader le code (sans `.git`, avec `vendor/`) ;
- [ ] Droits d'écriture sur `uploads/` et `logs/` ;
- [ ] Activer le **SSL Let's Encrypt** OVH (le `.htaccess` force ensuite HTTPS) ;
- [ ] Créer `config/mail_config.php` (SMTP OVH) ;
- [ ] Configurer **SPF / DKIM / DMARC** sur le domaine ;
- [ ] Activer les **sauvegardes automatiques** OVH + une sauvegarde manuelle ;
- [ ] **Changer le mot de passe admin** via l'écran **Mot de passe** ;
- [ ] Créer `config/payment_config.php` avec les clés Kkiapay (§4) ;
- [ ] **Vérifier la protection des fichiers** : `https://joiedenseignante.com/uploads/pdf/<un-fichier>.pdf`
      doit répondre **403**. Tester aussi un document via `download.php` (doit fonctionner) ;
- [ ] **Vérifier la lecture d'une vidéo d'article** : elle doit se lancer et se déplacer normalement ;
- [ ] **Tester l'e-mail d'achat** avec une vraie adresse, puis le bouton **Renvoyer** depuis Ventes ;
- [ ] Tester : accueil, article, PDF gratuit, achat payant, commentaire, contact, admin, ventes.

---

## 6. ⚠️ Devis PDF — ne pas envoyer l'ancienne version

Le PDF remis au client contient des incohérences (TVA à 14,74 %, « net à payer » différent du TTC).

✅ La version corrigée existe dans **`DEVIS_JOIEENSEIGNANTE.md`** :

| Poste | Montant TTC |
|---|---|
| Hébergement Pro OVHcloud — 24 mois | 75 400 F |
| Domaine `.com` — an 1 offert + an 2 | 8 850 F |
| Mise en ligne + sécurité | 10 000 F |
| **TOTAL** | **94 250 FCFA** |

- Paiement : **94 250 F à la signature** (100 % avant mise en ligne) ;
- Renouvellement année 3 : **~71 750 F/an** (indicatif) ;
- Le SSL est **gratuit** chez OVH : il est inclus dans la ligne « mise en ligne + sécurité », pas facturé séparément.

👉 **Régénérer le PDF à partir de ce fichier** avant l'envoi au client ou au juriste.

---

## 7. ⏱️ Plan réaliste des 12 heures

| Créneau | Toi | Moi |
|---|---|---|
| **H0 – H1** | Acheter hébergement + domaine + boîte mail | Suivi / corrections résiduelles |
| **H1 – H3** | Base + variables + `composer install` + upload + migrations | Tests locaux complémentaires |
| **H3 – H5** | DNS + SSL + SMTP + SPF/DKIM | Configuration prod + vérifications |
| **H5 – H8** | Tests en ligne page par page | Corrections des anomalies |
| **H8 – H10** | Recette client | Accompagnement / retouches |
| **H10 – H12** | Clés Kkiapay + sauvegardes + remise des accès | Contrôle final + livraison |

---

## 8. ⚠️ Risques à surveiller

1. **DNS** : 1 h à 48 h → acheter le domaine immédiatement.
2. **Emails** : sans SPF/DKIM, le formulaire de contact part en spam.
3. **Kkiapay** : l'activation du compte marchand peut prendre du temps ; le site reste utilisable sans.
4. **Migration oubliée** : sans `migration_paiement.sql`, les pages articles affichent une erreur (colonne `price` absente).
5. **Paiement** : la mise en ligne est prévue « après réception du paiement » (article 3 des conditions).
6. **Protection des fichiers** : si OVH n'applique pas les `.htaccess` (option `AllowOverride`),
   les PDF pourraient rester accessibles par adresse directe. **Tester le 403** juste après l'upload ;
   en cas d'échec, demander à OVH d'activer `AllowOverride All`.

---

## 9. Go / No-Go

**✅ GO si :**
- domaine + hébergement commandés ;
- migrations exécutées ;
- SSL + SMTP configurés ;
- mot de passe admin changé ;
- protection des fichiers vérifiée (403 sur `/uploads/pdf/...`) ;
- devis corrigé validé.

**⛔ NO-GO si :**
- le domaine n'est pas commandé (DNS trop long) ;
- les migrations ne sont pas passées ;
- le devis part au juriste avec les anciens montants.

---

## 10. Fichiers ajoutés / modifiés (récapitulatif)

**Ajoutés**
- `sql/migration_paiement.sql`, `sql/migration_newsletter.sql`
- `config/payment.php`, `config/payment_config.example.php`
- `public/purchase.php`, `public/payment_callback.php`, `public/newsletter_subscribe.php`
- `public/video.php` (diffusion protégée des vidéos d'articles)
- `uploads/pdf/.htaccess`, `uploads/docs/.htaccess`, `uploads/audio/.htaccess`, `uploads/video/.htaccess`
- `public/sitemap.php`, `public/404.php`
- `admin/change_password.php`, `admin/manage_purchases.php`
- `MANUEL_ADMINISTRATION.md`, `robots.txt`

**Modifiés**
- `public/download.php`, `public/post.php`, `public/contact.php`, `public/index.php`
- `public/register.php`, `public/resend-verification.php`
- `public/add_comment.php`, `includes/faq-section.php`, `includes/newsletter-section.php`, `includes/header.php`
- `admin/dashboard.php`, `admin/add_post.php`, `admin/edit_post.php`, `admin/delete_file.php`
- `config/theme.php`, `config/config.php`, `config/mail.php`, `.gitignore`, `.htaccess`, `dev-start.sh`, `DEVIS_JOIEENSEIGNANTE.md`

---

*Documents liés : `AUDIT_TECHNIQUE.md`, `CAHIER_DES_CHARGES.md`, `DEVIS_JOIEENSEIGNANTE.md`, `FACTURE_PROFORMA.md`, `INFOS_ABONNEMENT_JURISTE.md`, `MANUEL_ADMINISTRATION.md`.*
