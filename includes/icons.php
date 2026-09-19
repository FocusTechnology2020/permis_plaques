<?php
/**
 * includes/icons.php
 * Petites icônes SVG en ligne (Heroicons-like) pour éviter toute dépendance
 * externe. Retournées sous forme de chaîne HTML prête à afficher.
 */

declare(strict_types=1);

function icon_svg(string $name): string
{
    $common = 'class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"';
    $paths = [
        'grid'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h6v6h-6v-6ZM14.25 4.5h6v6h-6v-6ZM3.75 13.5h6v6h-6v-6ZM14.25 13.5h6v6h-6v-6Z" />',
        'user'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />',
        'car'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5 5 7.5h14l2 6M3 13.5v4.5h2.25M3 13.5h18M20.25 18h.75v-4.5M6.75 18a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm13.5 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />',
        'id'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75ZM6.75 9.75a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm-.75 6c.3-1.5 1.687-2.25 3.75-2.25s3.45.75 3.75 2.25M13.5 9h5.25M13.5 12h5.25M13.5 15h3" />',
        'alert'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12V15.75Zm9.75-3.75a9.75 9.75 0 1 1-19.5 0 9.75 9.75 0 0 1 19.5 0Z" />',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="m12 3 7.5 3v5.25c0 4.72-3.18 8.77-7.5 10.02-4.32-1.25-7.5-5.3-7.5-10.02V6L12 3Z" />',
        'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M18.75 10.5a8.25 8.25 0 1 1-16.5 0 8.25 8.25 0 0 1 16.5 0Z" />',
        'plus'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />',
        'edit'   => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />',
        'trash'  => '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.77 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397M4.772 5.79A48.107 48.107 0 0 1 8.25 5.393m0 0V4.62c0-1.153.933-2.087 2.086-2.087h3.328c1.153 0 2.086.934 2.086 2.087v.772M8.25 5.393a48.108 48.108 0 0 1 7.5 0" />',
    ];
    $body = $paths[$name] ?? $paths['grid'];
    return '<svg ' . $common . '>' . $body . '</svg>';
}
