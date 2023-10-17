<?php

/*
 * © Copyright 2023 · Willem Vervuurt, Studio Delfuego
 * 
 * You can modify, use and distribute this package under one of two licenses:
 * 1. GNU AGPLv3
 * 2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want 
 *    license that allows both non-commercial and commercial use, under conditions.
 *    See LICENSE.md for details.
 * 
 *    (it boils down to: do what you want as long as you're building and/or
 *     using wizards, but don't embed this package or a modified version
 *     of it in free or paid-for software libraries and packages aimed at developers).
 */

use App\Nova\Wizards\AddUserWizard;

return [

    'add-user' => [

        /*
         * The class of the Nova Wizard.
           Don't forget to add the proper use statement above.
           This key is required.
         */
        'class' => AddUserWizard::class,

        /*
         * URI for this wizard (will be appended to the Nova path, /nova by default)
           This key is required.
         */
        'uri' => 'wizard/add-user',

        /*
         * Browser window/tab title for this Nova Wizard.
           This key is optional.
           If you remove it or set it to an empty string, the dynamic title displayed above
           the wizard view will be used as window/tab title in the browser.
         */
        'windowTitle' => 'Add User',

    ],
    
];