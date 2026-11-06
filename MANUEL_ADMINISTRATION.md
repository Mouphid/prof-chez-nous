# Manuel d'administration — Joie Enseignante

Ce guide est destiné au professeur (administrateur du site). Il explique, pas à pas, comment gérer le site au quotidien.

Adresse d'administration : **https://joiedenseignante.com/admin/login.php**

---

## 1. Se connecter

1. Ouvrir `https://joiedenseignante.com/admin/login.php`.
2. Saisir l'email et le mot de passe fournis lors de la livraison.
3. Cliquer sur **Se connecter**.

> ⚠️ **Avant la mise en ligne**, changer le mot de passe par défaut (voir §2).

---

## 2. Changer le mot de passe (à faire en premier)

1. Dans le menu de gauche, cliquer sur **Mot de passe**.
2. Saisir le mot de passe actuel.
3. Choisir un nouveau mot de passe de **10 caractères minimum**.
4. Le confirmer, puis cliquer sur **Enregistrer le nouveau mot de passe**.

Conseil : utiliser une phrase longue et unique (par exemple 4 mots + un chiffre), et la conserver dans un gestionnaire de mots de passe.

---

## 3. Publier un article

1. Menu de gauche → **Nouveau post**.
2. Remplir :
   - **Titre** ;
   - **Contenu** ;
   - **Catégorie** ;
   - **Statut** : Brouillon (invisible), Publié, ou Programmé.
3. Si le statut est **Programmé**, choisir la date et l'heure de publication.
4. Cliquer sur **Ajouter le post**.

Les articles se modifient ensuite via **Articles → Modifier**.

---

## 4. Joindre un document PDF (gratuit ou payant)

### Ajouter le document

1. Créer ou modifier un article.
2. Dans **Ressources attachées**, choisir un ou plusieurs fichiers (PDF, Word, Excel, audio, image).
3. Renseigner **Prix des fichiers joints (FCFA)** :
   - **0** = document **gratuit** → bouton « Télécharger » direct ;
   - **un montant** (ex. `2500`) = document **payant** → bouton « Acheter » avec paiement en ligne.
4. Enregistrer.

### Modifier le prix d'un document déjà en ligne

1. **Articles → Modifier** l'article concerné.
2. La liste des fichiers attachés affiche **Gratuit** ou **Payant**.
3. Modifier le champ **Prix (FCFA)** à côté du fichier.
4. Cliquer sur **Enregistrer**.

> Un document payant n'est téléchargeable qu'après un paiement confirmé. Le lien de téléchargement est alors unique et automatique.

---

## 5. Modérer les commentaires

Les commentaires envoyés par les visiteurs sont **en attente** par défaut.

1. Menu → **Commentaires**.
2. Onglet **En attente**.
3. Pour chaque message : **Approuver** (publication) ou **Supprimer**.

---

## 6. Suivre les ventes de documents

1. Menu → **Ventes**.
2. Voir le nombre de commandes, les paiements encaissés et le total en FCFA.
3. Filtrer par statut : Payées, En attente, Échouées, Remboursées.
4. Chaque ligne indique le document, l'acheteur (nom, email, téléphone), le montant et l'identifiant de transaction.

> Un paiement « en attente » signifie que le client a ouvert la fenêtre de paiement mais n'a pas terminé. « Échoué » signifie que la vérification serveur n'a pas confirmé la transaction.

---

## 7. Activer le paiement en ligne (Kkiapay)

Le module est déjà installé, mais il attend les clés du compte marchand.

1. Créer/activer un compte marchand sur [kkiapay.me](https://kkiapay.me).
2. Dans le tableau de bord Kkiapay → menu **Développeurs**, récupérer :
   - la clé publique ;
   - la clé privée ;
   - la clé secrète.
3. Sur le serveur, copier `config/payment_config.example.php` vers `config/payment_config.php`.
4. Renseigner les trois clés.
5. Mettre `'sandbox' => false` pour les paiements réels (`true` = mode test).
6. Tester un achat réel de faible montant, puis vérifier qu'il apparaît en **Payé** dans **Ventes**.

> Sans clés, les pages de documents payants affichent « Paiement en ligne bientôt disponible » avec un lien de contact. Aucune erreur ne bloque le site.

---

## 8. Formulaire de contact et emails

1. Créer une boîte mail `contact@joiedenseignante.com` chez OVH.
2. Sur le serveur, copier `config/mail_config.example.php` vers `config/mail_config.php`.
3. Renseigner l'hôte SMTP OVH, l'identifiant et le mot de passe de la boîte.
4. Vérifier que le domaine possède bien les enregistrements **SPF** et **DKIM** (dans l'espace client OVH).

Sans SPF/DKIM, les messages risquent d'arriver en spam.

---

## 9. Envoyer la newsletter

1. Menu → **Newsletter** : voir le nombre d'abonnés actifs, filtrer et rechercher une adresse.
2. Bouton **Exporter en CSV** : ouvre la liste dans Excel (une ligne par abonné).
3. Bouton **Écrire un message** : saisir un sujet et un message en texte simple.
4. Cliquer sur **Aperçu avant envoi** pour relire, puis **Envoyer maintenant**.
5. L'historique des derniers envois s'affiche en bas de la page Newsletter.

> Chaque e-mail contient automatiquement un lien de désinscription personnel.
> Une adresse désinscrite ne reçoit plus la newsletter, mais continue à recevoir
> les liens liés à ses achats.
> L'envoi réel nécessite le **SMTP** configuré (section 8).

---

## 10. Sauvegardes

- **Automatique** : activer les sauvegardes quotidiennes dans l'espace client OVH (hébergement + base de données).
- **Manuelle avant chaque grosse modification** :
  1. exporter la base depuis phpMyAdmin (format SQL) ;
  2. télécharger le dossier `uploads/` par FTP.

Conserver au moins une sauvegarde sur un support différent du serveur (ordinateur personnel ou disque externe).

---

## 11. En cas de problème

| Symptôme | Que faire |
|---|---|
| Impossible de se connecter | Vérifier l'email et le mot de passe ; attendre 5 minutes après 5 échecs (protection anti-attaques). |
| Un PDF ne se télécharge pas | Vérifier que le fichier existe dans `uploads/pdf/` et que son prix est correct. |
| Un paiement reste « en attente » | Le client n'a pas terminé le paiement. Il peut réessayer depuis l'article. |
| Les emails de contact n'arrivent pas | Vérifier `config/mail_config.php` et les réglages SPF/DKIM. |
| Page blanche | Consulter les journaux dans `logs/` ou contacter le prestataire technique. |

---

## 12. Rôles et accès

- **Administrateur** : accès complet (articles, commentaires, catégories, utilisateurs, ventes, mot de passe).
- **Auteur** : articles et commentaires uniquement.
- **Étudiant** : accès limité (non utilisé dans la configuration actuelle).

Pour donner un accès : **Utilisateurs → Ajouter un utilisateur**.

---

*Document remis avec le site. Version du 6 novembre 2026.*
