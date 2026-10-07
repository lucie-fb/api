<?php

namespace App\Exception\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EmailAlreadyUsedException extends HttpException
{
    /**
     * Signals a registration refused because the email is already taken.
     */
    public function __construct()
    {
        // le message ne porte pas l'adresse : il finirait dans le journal,
        // que la règle de cette étape veut vide de toute donnée personnelle
        parent::__construct(Response::HTTP_CONFLICT, 'Adresse email déjà utilisée');
    }
}
