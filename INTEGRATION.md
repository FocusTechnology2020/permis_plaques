# Intégration — écran d'ouverture + mode sombre

## 1. Copier les fichiers

```
permis-plaques/
├── welcome.php                  ← nouveau (splash + 3 écrans + « Commencer »)
├── assets/logo.svg              ← nouveau
└── includes/
    ├── theme.php                ← nouveau (Tailwind + thème sombre)
    └── theme_toggle.php         ← nouveau (bouton clair/sombre)
```

## 2. `includes/header.php` — remplacer le chargement de Tailwind

Dans le `<head>`, supprimez la ligne :

```html
<script src="https://cdn.tailwindcss.com"></script>
```

et mettez à la place :

```php
<?php include __DIR__ . '/theme.php'; ?>
```

Faites de même dans `login.php` et `register.php` s'ils ont leur propre `<head>`
(le fichier se protège contre une double inclusion, aucun risque).

Ajoutez aussi le favicon pendant que vous y êtes :

```html
<link rel="icon" href="assets/logo.svg" type="image/svg+xml">
```

## 3. Le bouton de bascule

Dans la barre du haut de `includes/header.php`, à côté du nom de l'utilisateur :

```php
<?php include __DIR__ . '/theme_toggle.php'; ?>
```

Et pour que les pages publiques en profitent aussi, le même include dans
`login.php` / `register.php`.

## 4. `index.php` — envoyer les visiteurs vers l'accueil

Là où un visiteur non connecté est redirigé vers `login.php`, redirigez-le
plutôt vers `welcome.php` :

```php
if (!est_connecte()) {            // adaptez au nom réel de votre fonction
    header('Location: welcome.php');
    exit;
}
```

`welcome.php` se charge du reste : il rejoue le splash, affiche la présentation
la première fois, puis n'envoie plus que vers `login.php` les fois suivantes.
Si quelqu'un est déjà connecté, il est renvoyé directement vers `index.php`.

## Détails utiles

- **Revoir la présentation** : `welcome.php?replay=1`. L'état « déjà vu » est
  stocké dans `localStorage` sous la clé `pp.onboarding.vu`.
- **Durée du splash** : constante `2300` (ms) dans le script en bas de
  `welcome.php`. Un clic ou une touche le passe immédiatement.
- **Choix du thème** : clé `pp.theme` (`light` / `dark`). Tant que rien n'est
  choisi, l'application suit le réglage du système. Le script anti-flash de
  `theme.php` applique la classe `dark` avant le premier rendu, donc pas
  d'éclair blanc au chargement.
- **Le mode sombre repeint vos pages existantes** sans les modifier : une
  couche CSS traduit les classes déjà utilisées (`bg-white`, `text-gray-800`,
  `border-gray-200`, `thead`, `input`…). Les sélecteurs sont écrits avec
  `html:where(.dark)`, de spécificité volontairement faible : le jour où vous
  ajoutez une classe `dark:bg-...` sur un élément, c'est elle qui l'emporte.
- **Accessibilité** : `prefers-reduced-motion` coupe les animations du splash,
  les flèches ←/→ et le glissement tactile font défiler les écrans, et le
  contour de focus jaune reste visible dans les deux thèmes.

## Ce qui reste à faire de votre côté

Les points de la section « production » du README tiennent toujours (MySQL +
PDO, `password_hash()`, jeton CSRF). Le mode sombre et l'onboarding sont
purement côté affichage : ils ne touchent ni à `includes/data.php` ni à
`includes/auth.php`.
