<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Cart\CartPayInput;
use App\Dto\Cart\CartPayOutput;
use App\Service\CartService;

/**
 * @implements ProcessorInterface<CartPayInput, CartPayOutput>
 */
final class CartPayProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    /**
     * Pays the cart carried by the URL, and serves the confirmation with the tickets issued.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CartPayOutput
    {
        // $data porte le moyen de paiement, déjà validé par ses contraintes : le règlement étant
        // simulé, plus personne ne le lit. Le panier se retrouve par l'URL, comme à l'ajout de ligne
        $cart = $this->cartService->findOneById($uriVariables['id']);

        return $this->cartService->toPayment($cart, $this->cartService->pay($cart));
    }
}
