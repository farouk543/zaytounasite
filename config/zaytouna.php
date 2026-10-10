<?php

return [
    /*
    | Adresses qui reçoivent les e-mails d'administration (nouvel inscrit,
    | nouvelle demande d'abonnement…), en plus des comptes ayant le rôle admin.
    | Liste séparée par des virgules.
    */
    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_NOTIFICATION_EMAILS', ''))
    ))),

    // Plateforme principale (cours live) où les comptes élèves sont créés.
    'platform_url' => env('PLATFORM_URL'),

    // Coordonnées affichées aux parents dans les e-mails.
    'contact_phone' => env('CONTACT_PHONE'),
    'contact_email' => env('CONTACT_EMAIL'),
];
