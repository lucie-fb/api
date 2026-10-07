<?php

namespace App\Exception\Cart;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CartLineNotFoundException extends HttpException
{
    /**
     * Signals a lookup on a cart line identifier that matches nothing in this cart.
     */
    public function __construct()
    {
        // le message ne porte pas l'identifiant : il finirait dans le journal
        parent::__construct(Response::HTTP_NOT_FOUND, 'Ligne de panier non trouvée');
    }
}
