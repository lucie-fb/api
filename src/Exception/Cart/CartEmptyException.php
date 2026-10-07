<?php

namespace App\Exception\Cart;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CartEmptyException extends HttpException
{
    /**
     * Signals an attempt to pay a cart that carries no line.
     */
    public function __construct()
    {
        // le message ne porte pas l'identifiant du panier : il finirait dans le journal
        parent::__construct(Response::HTTP_CONFLICT, 'Panier vide');
    }
}
