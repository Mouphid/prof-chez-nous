# JoieEnseignante - Blog Professeur Universitaire

Blog académique pour un professeur universitaire spécialisé en littérature.

## Installation

1. **Créer la base de données**
   - Nom : `joieenseignante`
   - Importez le fichier SQL

2. **Configuration**
   - Fichier : `config/config.php`
   - Utilisateur : `root`
   - Mot de passe : vide (défaut XAMPP)

3. **Accès**
   - Blog public : `http://localhost/JoieEnseignante/public/`
   - Admin : `http://localhost/JoieEnseignante/admin/`

4. **Comptes**
   - **Admin** : admin@joieenseignante.com / admin123
   - **Utilisateurs** : s'inscrivent via `/public/register.php`

---

## Structure du projet

```
JoieEnseignante/
├── admin/                   # Panel d'administration (avec permissions)
│   ├── config_admin.php     # Système de permissions par rôle
│   ├── dashboard.php        # Tableau de bord adaptatif
│   ├── manage_users.php     # Gestion utilisateurs (admin uniquement)
│   ├── manage_posts.php     # Articles (admin + auteurs)
│   ├── manage_comments.php  # Commentaires (admin + auteurs)
│   ├── manage_categories.php# Catégories (admin uniquement)
│   ├── add_post.php         # Créer un article
│   ├── edit_post.php        # Modifier un article
│   ├── delete_post.php      # Supprimer un article
│   └── login.php            # Connexion admin
│
├── public/                  # Blog public / Utilisateurs
│   ├── index.php            # Page d'accueil (articles publiés uniquement)
│   ├── post.php             # Détail d'un article
│   ├── category.php         # Articles par catégorie
│   ├── resources.php        # Ressources téléchargeables
│   ├── search.php           # Recherche
│   ├── profile.php          # Dashboard utilisateur
│   ├── register.php         # Inscription
│   ├── login.php            # Connexion
│   ├── add_comment.php      # API commentaires
│   ├── download.php         # Téléchargement fichiers
│   └── manage_comment.php   # Modifier/supprimer commentaires
│
├── config/
│   └── config.php           # Configuration DB
│
├── includes/
│   └── functions.php        # Fonctions utilitaires
│
└── uploads/                # Fichiers uploadés (images, pdf, docs, audio, video)
```

---

## Système de permissions

### Rôles disponibles
| Rôle | Description |
|------|-------------|
| **admin** | Super administrateur - accès complet |
| **auteur** | Peut publier et gérer ses propres articles, modérer commentaires |
| **etudiant** | Peut lire, commenter, télécharger (accès public uniquement) |

### Tableau des permissions

| Permission | Admin | Auteur | Étudiant |
|------------|-------|--------|----------|
| Accéder au panel admin | ✅ | ✅ | ❌ |
| Voir le dashboard | ✅ | ✅ | ❌ |
| Publier des articles | ✅ | ✅ | ❌ |
| Modifier/supprimer ses propres articles | ✅ | ✅ | ❌ |
| Modifier/supprimer les articles des autres | ✅ | ❌ | ❌ |
| Gérer les utilisateurs (ajouter/modifier/supprimer) | ✅ | ❌ | ❌ |
| Attribuer des rôles (admin/auteur/étudiant) | ✅ | ❌ | ❌ |
| Gérer les catégories | ✅ | ❌ | ❌ |
| Modérer les commentaires | ✅ | ✅ | ❌ |
| Télécharger les ressources | ✅ | ✅ | ✅ |
| Commenter les articles | ✅ | ✅ | ✅ |

### Règles importantes
- **Un admin ne peut pas être supprimé par un auteur**
- **Un utilisateur ne peut pas changer son propre rôle**
- **Les auteurs ne voient que leurs propres articles** dans le panel
- **Seul le super admin** peut ajouter/modifier/supprimer des utilisateurs

---

## Tables de la base de données

| Table | Description |
|-------|-------------|
| `users` | Utilisateurs (id_user, name, email, password, role, bio, avatar, is_active) |
| `posts` | Articles (id_post, title, content, id_user, id_category, status) |
| `categories` | Catégories (Littérature, Didactique, etc.) |
| `comments` | Commentaires (id_comment, id_post, id_user, author_name, content, token_hash) |
| `likes` | J'aime (id_post, ip_address) |
| `files` | Fichiers attachés (id_file, id_post, file_name) |
| `article_reads` | Articles lus par utilisateur (id_user, id_post) |
| `user_downloads` | Téléchargements utilisateur (id_user, id_file) |

---

## Fonctionnalités

### Dashboard Utilisateur (`/public/profile.php`)
- Articles lus (compteur)
- Téléchargements (compteur + liste)
- Statut "En ligne"
- Activité récente
- Modifier profil (nom, bio, photo)

### Dashboard Admin (`/admin/dashboard.php`)
- Statistiques dynamiques selon le rôle
- Menu adaptatif (masque les sections non autorisées)
- Tableau des permissions visible

### Sécurité
- Prepared statements PDO
- XSS protection avec `htmlspecialchars()`
- Sessions sécurisées
- Upload sécurisé avec validation MIME
- Système de tokens pour commentaires

---

## Auteur

Developpé pour le département de Littérature, Université de Cotonou