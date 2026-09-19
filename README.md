# Permis & Plaques — Dashboard PHP

Prototype fonctionnel d'une application de contrôle des permis de conduire et
des plaques d'immatriculation, avec deux espaces : **Administration** et
**Agent de contrôle**.

## Stack

- PHP 8+ (aucune extension particulière requise)
- Tailwind CSS (via CDN)
- JavaScript vanilla (juste quelques interactions simples)
- Pas de base de données : les données de démonstration sont stockées dans la
  **session PHP** (`$_SESSION['db']`). Chaque nouvelle session repart avec le
  jeu de données d'exemple.

## Lancer le prototype

Aucune installation n'est nécessaire à part PHP.

```bash
cd permis-plaques
php -S localhost:8000
```

Puis ouvrez `http://localhost:8000/` dans votre navigateur.

## Comptes de démonstration

| Rôle              | Identifiant                     | Mot de passe |
|--------------------|----------------------------------|---------------|
| Super administrateur | `admin`                        | `admin123`    |
| Agent de démonstration | `agent@permis-plaques.local` | `agent123`    |

Le formulaire d'inscription (`register.php`) permet de créer de nouveaux
comptes **agent** (nom, email, mot de passe). Un administrateur peut ensuite
promouvoir un agent au rôle « Administrateur » depuis **Agents & rôles**, ou
désactiver un compte.

## Structure du projet

```
permis-plaques/
├── config.php              Bootstrap (session, constantes, includes)
├── login.php / register.php / logout.php
├── index.php                Redirige vers le bon tableau de bord selon le rôle
├── includes/
│   ├── data.php              Couche "données" (CRUD sur $_SESSION['db']) + seed
│   ├── auth.php               Authentification / garde-fous de rôle
│   ├── icons.php               Petites icônes SVG en ligne
│   ├── header.php / sidebar.php / footer.php   Layout commun
├── admin/
│   ├── dashboard.php          Statistiques (permis, infractions, agents…)
│   ├── drivers.php            CRUD conducteurs
│   ├── vehicles.php           CRUD plaques / véhicules
│   ├── permits.php            CRUD permis + suspension / expiration
│   ├── infractions.php        Historique complet + changement de statut
│   └── agents.php             Gestion des comptes agents/admins et des rôles
└── agent/
    ├── search.php              Recherche par plaque ou numéro de permis
    ├── infraction_new.php       Enregistrement d'une infraction + sanction
    └── history.php               Historique des infractions (par conducteur ou global)
```

## Modules

### Admin
- Tableau de bord avec statistiques (conducteurs, véhicules, permis
  valides/expirés/suspendus, infractions du mois, agents actifs)
- Gestion des conducteurs (CRUD)
- Gestion des plaques / véhicules (CRUD, liés à un propriétaire)
- Gestion des permis et de leur état (valide / expiré calculé automatiquement
  à partir de la date d'expiration, ou suspendu manuellement)
- Historique des infractions (recherche, filtre par statut, marquage
  traitée/en attente)
- Gestion des agents et des rôles (création, promotion/rétrogradation,
  activation/désactivation, suppression)

### Agent de contrôle
- Recherche par plaque **ou** numéro de permis
- Vérification du conducteur et de l'état de son permis
- Consultation de l'historique des infractions (du conducteur ou global)
- Enregistrement d'une infraction avec description
- Sélection d'une sanction dans une grille prédéfinie (amende + points)

## ⚠️ Pour une vraie version (production)

Ce prototype utilise des sessions PHP comme "base de données" volatile et des
mots de passe en clair, uniquement à des fins de démonstration. Pour une
version de production, il faudra a minima :

- **MySQL + PDO** : remplacer `includes/data.php` par une vraie couche
  d'accès aux données avec requêtes préparées (protection contre les
  injections SQL).
- **`password_hash()` / `password_verify()`** : ne plus jamais stocker ou
  comparer de mots de passe en clair.
- **Protection CSRF** : jeton unique par formulaire, vérifié à chaque
  soumission POST.
- **Permissions serveur** : restreindre l'accès aux fichiers de
  configuration, séparer les identifiants de connexion à la base dans des
  variables d'environnement.
- **Journal d'audit** : tracer qui a créé/modifié/supprimé quoi et quand
  (conducteurs, permis, infractions, comptes).
- **Une vraie table de sanctions / règles routières**, idéalement gérée par
  un référentiel légal à jour et versionné, avec traçabilité des montants
  d'amende appliqués.
- Autres bonnes pratiques : limitation du taux de connexion (anti
  brute-force), validation/assainissement plus stricte des entrées,
  HTTPS obligatoire, expiration de session, rôles plus granulaires
  (permissions par action plutôt que par simple admin/agent).
