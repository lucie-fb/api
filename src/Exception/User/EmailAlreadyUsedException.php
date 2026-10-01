<?php

namespace App\Exception\User;

class EmailAlreadyUsedException extends \RuntimeException
{
    /**
     * Signals a registration refused because the email is already taken.
     */
    public function __construct()
    {
        // le message ne porte pas l'adresse : il finirait dans le journal,
        // que la règle de cette étape veut vide de toute donnée personnelle
        parent::__construct('This email address is already registered.');
    }
}
