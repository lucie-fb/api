<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Cart;
use App\Service\CartService;

/**
 * @implements ProviderInterface<Cart>
 */
final class CartProvider implements ProviderInterface
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): null|Cart
    {
        return $this->cartService->findOneById($uriVariables['id']);
    }
}
