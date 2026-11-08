# Fiche technique — Favicon et logo

Document à transmettre au graphiste.
Le site est déjà branché : il suffit de remplacer les fichiers dans le dossier
`img/` en gardant **exactement les mêmes noms**.

---

## 1. Favicon : le plus simple à demander

Fournir **un seul fichier maître carré** :

| Élément | Valeur |
|---|---|
| Format | PNG avec transparence (ou SVG) |
| Taille | **1024 × 1024 px** (carré parfait) |
| Fond | transparent, ou fond blanc arrondi si le graphiste préfère |
| Poids | moins de 200 Ko |
| Zone de sécurité | le symbole doit tenir dans les **80 % centraux** (les bords peuvent être rognés par certains navigateurs) |

À partir de ce fichier, je génère automatiquement toutes les tailles.
Pas besoin de livrer 8 fichiers différents.

### Si le graphiste préfère livrer chaque taille

| Nom du fichier | Taille | Usage |
|---|---|---|
| `favicon-16.png` | 16 × 16 | onglet du navigateur |
| `favicon-32.png` | 32 × 32 | onglet (écrans haute densité) |
| `favicon-48.png` | 48 × 48 | favoris Windows |
| `favicon-64.png` | 64 × 64 | favoris macOS |
| `apple-touch-icon.png` | 180 × 180 | écran d’accueil iPhone / iPad |
| `favicon-192.png` | 192 × 192 | Android / PWA |
| `favicon-512.png` | 512 × 512 | Android / PWA haute résolution |
| `favicon.ico` | 16 + 32 + 48 | anciens navigateurs (un seul fichier .ico) |

⚠️ Le favicon doit rester **lisible à 16 px**. Éviter les détails fins et le
texte : uniquement le symbole (monogramme ou pictogramme).

⚠️ Il doit être visible **sur fond clair et sur fond sombre** : un pictogramme
blanc seul disparaît sur les onglets blancs. Solution recommandée : un carré
blanc arrondi avec le symbole coloré au centre.

---

## 2. Logo

| Élément | Valeur |
|---|---|
| Format | SVG de préférence, sinon PNG avec transparence |
| Dimensions | ratio large, environ **2:1** (ex. 1880 × 940 px) |
| Variantes nécessaires | **3** : noir, blanc, gris moyen |
| Fond | toujours transparent (aucun fond blanc ni coloré) |
| Marge interne | prévoir une petite marge autour du dessin |

Les 3 variantes servent à :

- **noir** : pages blanches (connexion admin) ;
- **gris** : site en thème clair (rendu plus doux) ;
- **blanc** : site en thème sombre et barre latérale bleu nuit de l’admin.

### Tailles d’affichage réel

| Endroit | Hauteur affichée |
|---|---|
| En-tête du site | 40 px (mobile) / 48 px (ordinateur) |
| Pied de page | 40 px |
| Barre latérale admin | 32 px |
| Connexion admin | 64 px / 128 px |

Le logo doit donc rester lisible à **32–40 px de haut**. Si le nom complet est
écrit dans le logo, prévoir une version « symbole seul » pour les petites tailles.

---

## 3. Où déposer les fichiers

Dossier : `img/`

```
img/favicon-16.png
img/favicon-32.png
img/favicon-48.png
img/favicon-64.png
img/apple-touch-icon.png
img/favicon-192.png
img/favicon-512.png
img/favicon.ico

img/logo-noir.png
img/logo-gris.png
img/logo-blanc.png
```

Une fois les fichiers remplacés, vider le cache du navigateur
(**Ctrl + F5**) pour voir le nouveau favicon.

---

## 4. Rappel des couleurs du site

Ces valeurs sont celles réellement utilisées par le site. Le graphiste peut
s'en servir pour rester cohérent.

| Rôle | Couleur |
|---|---|
| Accent public (doré doux) | `#B3936B` |
| Bleu principal | `#32608C` |
| Bleu nuit (admin) | `#1E3A5F` |
| Symbole du favicon actuel (vert d'eau) | `#008080` |
| Fond clair | `#FFFFFF` / `#F9FAFB` |
| Fond sombre | `#111827` |

> Le logo est aujourd'hui composé du symbole **vert d'eau `#008080`** et du
> texte. Le graphiste peut conserver cette couleur ou proposer une variante
> assortie au doré `#B3936B`, à valider avant remplacement des fichiers.
