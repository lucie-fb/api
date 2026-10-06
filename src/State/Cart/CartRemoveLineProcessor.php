<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Cart\CartDetailsOutput;
use App\Entity\Cart;
use App\Service\CartService;

/**
 * @implements ProcessorInterface<Cart, null>
 */
final readonly class CartRemoveLineProcessor implements ProcessorInterface
{
    public function __construct(
        private CartService $cartService,
    ) {
    }

    /**
     * Serves the pending cart of the authenticated traveller, opening one when there is none.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?CartDetailsOutput
    {
        $cart = $this->cartService->findOneById($uriVariables['id']);

        $this->cartService->removeLine($cart, $uriVariables['itemId']);

        return null;
    }
}
