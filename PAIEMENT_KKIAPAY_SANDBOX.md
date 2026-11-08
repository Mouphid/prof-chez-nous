# Tester Kkiapay en mode sandbox

Ce guide sert à vérifier tout le paiement **sans encaisser d’argent réel**.
« Sandbox » veut dire « bac à sable » : un environnement de test fourni par
Kkiapay, avec de faux numéros de téléphone et de fausses transactions.

> Documentation officielle : <https://docs.kkiapay.me/v1/compte/kkiapay-sandbox-guide-de-test>
> Serveur de test : `https://api-sandbox.kkiapay.me`

---

## 1. Récupérer les 3 clés de test

1. Se connecter sur <https://kkiapay.me> (créer un compte si besoin, c’est gratuit).
2. Dans le tableau de bord, **activer le mode Sandbox** (interrupteur en haut).
3. Ouvrir **Développeurs → Clés API**.
4. Copier les trois clés affichées :
   - **clé publique** (public key) : ouvre la fenêtre de paiement ;
   - **clé privée** (private key) : vérification côté serveur ;
   - **clé secrète** (secret key) : vérification côté serveur.

⚠️ Les clés du sandbox sont **différentes** des clés réelles. Ne jamais publier
ces clés ni les envoyer dans un message public.

---

## 2. Renseigner le fichier local

Ouvrir `config/payment_config.php` (ce fichier n’est jamais envoyé sur GitHub)
et remplir les trois lignes :

```php
'kkiapay' => [
    'public_key'  => 'COLLER_LA_CLE_PUBLIQUE',
    'private_key' => 'COLLER_LA_CLE_PRIVEE',
    'secret_key'  => 'COLLER_LA_CLE_SECRETE',
],
```

Vérifier que le mode test est bien activé :

```php
'sandbox' => true,
```

Puis contrôler la configuration :

```bash
php tools/kkiapay_check.php
```

Résultat attendu :

```
Prestataire : Kkiapay
Mode        : SANDBOX (test)
Devise      : XOF
Clés        : publique seulement
Manque      : clé privée, clé secrète
Serveur     : https://api-sandbox.kkiapay.me
```

> La clé publique suffit pour tester : l’API sandbox de Kkiapay accepte de
> vérifier une transaction avec elle seule (vérifié le 8 novembre 2026).
> La clé privée et la clé secrète restent **recommandées** : la documentation
> Kkiapay les exige pour la production. Générez-les via
> **Développeurs → Clés API → « Générer de nouveaux »**.

---

## 3. Tester un achat complet dans le navigateur

Le site local tourne sur <http://127.0.0.1:8080>.

1. Choisir un document ou un cours **payant**. En local, les prix sont à 0 :
   mettre temporairement un prix de **100 FCFA** dans **Admin → Articles**
   (prix du document) ou **Admin → Cours**.
2. Ouvrir la page d’achat, remplir nom, e-mail et téléphone, puis cliquer sur
   **Continuer vers le paiement**.
3. Cliquer sur **Payer maintenant** : la fenêtre Kkiapay s’ouvre en mode test.
4. Utiliser un **numéro de test** (Bénin) :

| Scénario | Numéro MTN Bénin |
|---|---|
| Paiement réussi | `22997000000` |
| Paiement réussi après 1 minute | `22997100000` |
| Paiement réussi après 2 minutes | `22997200000` |
| Erreur de traitement | `22997000001` |
| Fonds insuffisants | `22997000002` |
| Paiement refusé | `22997000003` |

Numéros Moov Bénin équivalents : `22995000000` (succès), `22995000002`
(fonds insuffisants), etc.

5. Après le paiement réussi, le site doit :
   - afficher ou rediriger vers le document / le cours ;
   - créer un **lien privé** (`download_token`) ;
   - passer la commande en **Payé** dans **Admin → Ventes** ;
   - tenter d’envoyer l’e-mail. Sans SMTP configuré, l’envoi échoue mais
     **l’accès reste ouvert** (l’échec est écrit dans `logs/`).

### Vérifier en base

```bash
mysql -u root joieenseignante -e \
  "SELECT id_purchase, status, amount, transaction_id, paid_at FROM purchases ORDER BY id_purchase DESC LIMIT 5;"
```

Attendu : `status = paid`, `transaction_id` renseigné, `paid_at` non vide.

### Vérifier la transaction côté serveur

Copier l’identifiant de transaction affiché dans la base, puis :

```bash
php tools/kkiapay_check.php ID_TRANSACTION
```

Attendu : `Réussie : OUI`, `status : SUCCESS`, montant identique à la commande.

---

## 4. Tester un échec

Refaire un achat avec le numéro `22997000002` (fonds insuffisants).

Attendu :
- la fenêtre Kkiapay affiche l’échec ;
- la commande reste ou n’est pas marquée « Payé » ;
- **aucun** lien de téléchargement n’est créé ;
- le journal `logs/` contient une ligne « Paiement non confirmé ».

> Le site réessaie automatiquement la vérification pendant environ 10 secondes
> (0, 2, 3 puis 5 s). Si la transaction est encore « en attente », la commande
> **n’est pas** marquée comme échouée : une page « Paiement en cours de
> vérification » propose de revérifier.

### Résultat du test réalisé le 8 novembre 2026

| Étape | Résultat |
|---|---|
| Ouverture de la fenêtre Kkiapay | ✅ « Vous êtes en mode Sandbox », 102 FCFA (100 + frais) |
| Numéro `22997000000` | ✅ transaction `SUCCESS`, montant 100 |
| Commande en base | ✅ `status = paid`, `transaction_id` et `download_token` créés |
| Téléchargement du PDF | ✅ HTTP 200, fichier PDF de 8 pages |
| Sans le lien privé | ✅ accès refusé (redirection) |
| Numéro `22997000002` | ✅ « Fonds insuffisants », aucun lien créé |
| Admin → Ventes | ✅ ligne « Payé » + bandeau « Mode test Kkiapay » |

---

## 5. Tester le webhook (facultatif)

Le webhook est l’appel que Kkiapay fait à votre serveur en arrière-plan.
Il faut une adresse publique : `localhost` n’est pas joignable par Kkiapay.

Deux solutions :
- utiliser un tunnel de test (`ngrok`, `cloudflared`) vers le port 8080 ;
- tester directement sur le futur domaine après mise en ligne.

Adresse à déclarer dans **Kkiapay → Développeurs → Webhook** :

```
https://VOTRE-DOMAINE/public/payment_callback.php
```

Le webhook et le retour du navigateur peuvent arriver tous les deux :
le code est protégé contre le double paiement (une seule mise à jour en
« Payé », un seul e-mail).

---

## 6. Passer en paiement réel

Quand les tests sont concluants :

1. Demander l’**activation du compte marchand** (KYC) dans le tableau de bord.
2. Récupérer les **clés réelles** (mode Live).
3. Dans `config/payment_config.php` :
   - remplacer les 3 clés par les clés réelles ;
   - passer `'sandbox' => false`.
4. Vérifier :

```bash
php tools/kkiapay_check.php
```

Attendu : `Mode : PRODUCTION (argent réel)` et serveur `https://api.kkiapay.me`.

5. Faire un achat réel de faible montant, puis vérifier **Admin → Ventes**.

> Tant que `sandbox` reste à `true`, un bandeau orange « Mode test Kkiapay »
> s’affiche en haut de chaque page d’administration.

---

## 7. En cas de problème

| Symptôme | Cause probable | Solution |
|---|---|---|
| « Paiement en ligne bientôt disponible » | clés vides ou incomplètes | remplir les 3 clés dans `config/payment_config.php` |
| La fenêtre Kkiapay ne s’ouvre pas | clé publique incorrecte | recopier la clé publique du sandbox |
| `HTTP 401` ou `403` | clé privée / secrète incorrecte | recopier les clés, vérifier qu’on est bien en mode Sandbox |
| `Réussie : NON` avec `status` différent de `SUCCESS` | paiement non abouti ou numéro d’échec | refaire le test avec `22997000000` |
| « transaction déjà utilisée » | même transaction rejouée | utiliser l’identifiant d’une nouvelle transaction |
| Montant refusé | montant confirmé inférieur au prix | vérifier le prix et le montant envoyé au widget |
| E-mail non reçu | SMTP non configuré | normal en local ; l’accès reste disponible |
