<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Policies Configuration
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient la configuration des policies d'autorisation
    | pour l'application FjordLoop.
    |
    */

    'travel' => [
        'view_any' => true,
        'create' => true,
        'view' => 'member',
        'update' => 'member',
        'delete' => 'member',
        'invite_members' => 'member',
        'manage_members' => 'member',
        'create_activity' => 'member',
        'create_housing' => 'member',
    ],

    'activity' => [
        'view_any' => true,
        'view' => 'travel_member',
        'create' => 'travel_member',
        'update' => 'travel_member',
        'delete' => 'travel_member',
    ],

    'housing' => [
        'view_any' => true,
        'view' => 'travel_member',
        'create' => 'travel_member',
        'update' => 'travel_member',
        'delete' => 'travel_member',
    ],

    /*
    |--------------------------------------------------------------------------
    | Règles d'autorisation personnalisées
    |--------------------------------------------------------------------------
    |
    | Vous pouvez définir des règles d'autorisation personnalisées ici.
    | Ces règles peuvent être utilisées dans les policies pour des cas spéciaux.
    |
    */

    'custom_rules' => [
        'owner_only' => [
            'description' => 'Seuls les propriétaires du voyage peuvent effectuer cette action',
            'logic' => 'Vérifier is_owner dans la table pivot travel_user',
        ],
        'admin_override' => [
            'description' => 'Les administrateurs peuvent contourner certaines restrictions',
            'logic' => 'Vérifier le rôle admin de l\'utilisateur',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages d'erreur personnalisés
    |--------------------------------------------------------------------------
    |
    | Messages d'erreur affichés quand une autorisation est refusée.
    |
    */

    'error_messages' => [
        'travel' => [
            'view' => 'Vous n\'avez pas accès à ce voyage.',
            'update' => 'Vous ne pouvez pas modifier ce voyage.',
            'delete' => 'Vous ne pouvez pas supprimer ce voyage.',
            'invite_members' => 'Vous ne pouvez pas inviter de membres à ce voyage.',
            'manage_members' => 'Vous ne pouvez pas gérer les membres de ce voyage.',
        ],
        'activity' => [
            'view' => 'Vous n\'avez pas accès à cette activité.',
            'create' => 'Vous ne pouvez pas créer d\'activité pour ce voyage.',
            'update' => 'Vous ne pouvez pas modifier cette activité.',
            'delete' => 'Vous ne pouvez pas supprimer cette activité.',
        ],
        'housing' => [
            'view' => 'Vous n\'avez pas accès à ce logement.',
            'create' => 'Vous ne pouvez pas créer de logement pour ce voyage.',
            'update' => 'Vous ne pouvez pas modifier ce logement.',
            'delete' => 'Vous ne pouvez pas supprimer ce logement.',
        ],
    ],
];
