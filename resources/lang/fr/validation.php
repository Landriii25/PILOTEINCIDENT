<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lignes de Langage pour la Validation
    |--------------------------------------------------------------------------
    */

    'required' => 'Le champ :attribute est obligatoire.',
    'email'    => 'Le champ :attribute doit être une adresse email valide.',

    // AJOUTEZ CETTE LIGNE
    'unique'   => 'La valeur du champ :attribute est déjà utilisée.',

    'min'      => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    // Ajoutez d'autres messages ici au besoin

];
