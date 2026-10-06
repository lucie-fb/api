<?php

namespace App\Exception\Cart;

use App\Entity\Cart;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints\Uuid;

class CartNotFoundException extends HttpException
{
    /**
     * Signals a lookup on a city identifier that matches nothing.
     */
    public function __construct()
    {
        // le message ne porte pas l'identifiant : il finirait dans le journal
        parent::__construct(
            Response::HTTP_NOT_FOUND,
            'Panier non trouvé.');
    }

}
