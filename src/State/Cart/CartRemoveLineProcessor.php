<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Cart;
use App\Service\CartService;

/**
 * @implements ProcessorInterface<Cart, null>
 */
final class CartRemoveLineProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    /**
     * Removes the line carried by the URL from the cart carried by the URL, and serves no body.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        // les deux variables d'URL arrivent en UuidV7, pas en chaîne : le panier se retrouve du
        // même geste que le provider, la ligne se lit telle quelle
        $cart = $this->cartService->findOneById($uriVariables['id']);

        $this->cartService->removeLine($cart, $uriVariables['itemId']);

        // le 204 du contrat vient de ce null : l'opération ne fabrique aucun objet de sortie
        return null;
    }
}
