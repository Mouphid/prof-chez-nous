# 📁 DOSSIER INFOS — Abonnement « JoieEnseignante » 2 ans (pour le juriste)

> Document de travail destiné au juriste. Regroupe toutes les informations techniques,
> commerciales et factuelles pour rédiger le contrat d'abonnement (2 ans) et la mise en
> ligne du site, suite à la validation du Client (option `.com`).

---

## 0. Résumé de l'accord

| | |
|---|---|
| **Client** | Professeur de Littérature — Université de Cotonou, Bénin |
| **Prestataire** | Adonis — Développeur web indépendant |
| **Service** | Hébergement + domaine `.com` + mise en ligne du site « Joie Enseignante » |
| **Durée** | **24 mois** |
| **Prix total** | **94 250 FCFA TTC** (= 3 927 F/mois arrondi) |
| **Domaine** | `joiedenseignante.com` (le nom `joieenseignante.com` est déjà pris par un tiers) |
| **Règlement** | 100 % avant mise en ligne |

---

## 1. Détail du chiffrage

| # | Poste | Base OVHcloud (TTC) | Calcul FCFA (×656) | Montant arrondi |
|---|---|---|---|---|
| 1 | Hébergement Pro OVHcloud — 24 mois | 114,91 € | 75 381 | **75 400 F** |
| 2 | Domaine `.com` (an 1 offert, an 2 payant) | 0 + 13,49 € | 8 849 | **8 850 F** |
| 3 | Mise en ligne + sécurité (prestation dev) | — | — | **10 000 F** |
| | **TOTAL** | | | **94 250 FCFA** |

**Sources des prix (vérifiées en ligne, sept. 2026) :**
- OVHcloud — hébergement web Pro : tarif échelonné 24 mois.
- OVHcloud — nom de domaine `.com` : 7,99 € HT création (offert avec l'hébergement an 1), renouvellement 13,49 € HT/an.
- Taux de change rappel : **1 € = 655,957 F CFA** (parité fixe UEMOA). Le devis utilise 656 F arrondu.

**Estimation année 3 (renouvellement, hors promo) — à prévoir dans le contrat :**
- Hébergement Pro ~7,99 €/mois × 656 F ≈ **62 900 F/an**
- Domaine `.com` 13,49 € × 656 F = **8 850 F/an**
- **Total ≈ 71 750 F/an** (indicatif, OVH susceptible de modifier ses tarifs).

---

## 2. Périmètre technique livré (fait actuellement)

Le site existe déjà et fonctionne localement. La mise en ligne consiste à :

1. **Hébergement** : mutualisé Pro OVHcloud (250 Go, SSD, sauvegardes).
2. **Domaine** : enregistrement `joiedenseignante.com`, DNS, email de contact.
3. **Installation** : PHP 8.x + MySQL 8, déploiement du code (pile : PHP/MySQL/HTML-CSS, sans framework).
4. **Sécurité** : certificat SSL (HTTPS), durcissement, protections formulaire (CSRF, rate-limit), mot de passe hashés (bcrypt).
5. **Base de données** : tables users / categories / posts / files / comments / likes / settings / article_reads / user_downloads.
6. **Interface admin** : le Client publie lui-même articles, cours, ressources et images.

---

## 3. Contenu du service pendant les 2 ans

**Inclus (déjà payé dans les 94 250 F) :**
- Hébergement et domaine actifs pendant 24 mois.
- Assistance technique (mise à jour de sécurité, dépannage, renouvellement admin).
- Sauvegardes et supervision de l'hosting.

**En dehors du périmètre (à mentionner au contrat pour éviter tout malentendu) :**
- Création de nouveaux contenus (le Professeur publie lui-même via l'admin).
- Refonte graphique, nouvelles fonctionnalités (évolutions = devis séparé).
- Rédaction, traduction, design graphique.

---

## 4. Éléments juridiques à aborder dans le contrat

### 4.1 Parties et objet
- Prestataire : personne physique / EI (à compléter : N° d'identification, contact).
- Client : M. Sylvestre Djouamon, Professeur de Littérature, Université de Cotonou.
- Objet : fourniture d'hébergement, d'attribution de nom de domaine et de mise en ligne, pour une durée déterminée de 24 mois.

### 4.2 Durée et renouvellement
- Durée ferme : **24 mois**, paiement d'avance.
- À l'expiration : proposition de renouvellement au tarif alors en vigueur (an 3 indicatif ~71 750 F/an).
- Aucune tacite reconduction sans accord écrit.

### 4.3 Prix et paiement
- Forfait **94 250 FCFA TTC**, 100 % réglé avant mise en ligne.
- Réparti par poste (hébergement 75 400 F / domaine 8 850 F / mise en ligne 10 000 F).

### 4.4 Propriété intellectuelle
- Les **contenus** publiés (articles, images, cours) : propriété du Client.
- Le **code** de la plateforme développé par le Prestataire : appartient au Prestataire ; licence d'utilisation au Client pendant la durée, non transférable, sauf accord écrit (achat du code).
- Le nom de domaine : enregistré **au nom du Client** (titulaire = Client), géré techniquement par le Prestataire.

### 4.5 Données personnelles (RGPD / loi INFO-2 Bénin)
- Base de données = données personnelles (nom, email, IP pour les likes/commentaires).
- Mentionner : finalité, minimum nécessaire, durée de conservation, accès réservé, modalités à la fin du contrat (export des données au Client).

### 4.6 Responsabilité / limites
- Garantie de disponibilité « au mieux de l'hébergeur » (pas de garantie de disponibilité contractuelle type SLA sauf précision).
- Exclusion : dommages indirects, perte de données (sauvegardes à la charge du prestataire pendant l'abonnement), indisponibilités imputables à l'hébergeur/tiers.

### 4.7 Confidentialité
- Le Prestataire s'engage à ne pas divulguer les accès, contenus et données du Client.

### 4.8 Résiliation
- Par le Client : pas de remboursement des montants engagés (avance).
- Par le Prestataire : manquement grave, impayé, usage illicite.
- Effets : transfert des accès, export des données, extinction de l'assistance.

### 4.9 Loi applicable et litiges
- Droit béninois ; tribunal compétent (Cotonou). Médiation/tentative amiable obligatoire avant toute action.

---

## 5. Dates clés et documents à joindre

- Devis n° JE-2026-002 signé (devise FCFA).
- Facture pro-forma / acompte 100 % avant mise en ligne.
- PV de mise en ligne (date, URL, checklist SSL/DNS/BDD).
- Identifiants OVHcloud transmis séparément au Client (ou en séquestre).

---

## 6. Points RESTANT À CONFIRMER avec le Client

- [ ] Identité exacte à facturer (personne physique / entrepreneur).
- [ ] Mode de paiement effectif (espèces / virement / Mobile Money).
- [ ] Adresse de facturation / livraison du contrat.
- [ ] Confirmation du domaine définitif : `joiedenseignante.com` (ou autre variante à vérifier : `joie-enseignante.com`, `joieenseignante.bj`).
- [ ] Qui gère les emails professionnels associés au domaine (0 adresse incluse par défaut).
- [ ] Option d'acquisition du code source (achat définitif) — à chiffrer séparément le cas échéant.

---

*Fiche établie le 28 septembre 2026. Montants à re-vérifier face aux factures réelles OVHcloud au moment de la commande (les prix publiés sont susceptibles d'évoluer).*