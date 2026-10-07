<?php

namespace App\Exception\Trip;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TripNotFoundException extends HttpException
{
    /**
     * Signals a lookup on a trip identifier that matches nothing.
     */
    public function __construct()
    {
        // le message ne porte pas l'identifiant : il finirait dans le journal
        parent::__construct(Response::HTTP_NOT_FOUND, 'Lancer non trouvé');
    }
}
