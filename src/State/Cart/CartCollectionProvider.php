<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Cart\CartDetailsOutput;
use App\Entity\User;
use App\Service\CartService;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<CartDetailsOutput>
 */
final class CartCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly CartService $cartService,
    ) {
    }

    /**
     * Serves the pending cart of the authenticated traveller, as a collection of zero or one.
     *
     * @return CartDetailsOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();

        // on ne protège pas une collection, on la filtre : sans porteur identifiable, elle est vide
        if (!$user instanceof User) {
            return [];
        }

        $cart = $this->cartService->findActiveFor($user);

        if (null === $cart) {
            return [];
        }

        // un state ne fabrique aucun DTO : il appelle la méthode du service qui le fabrique
        return array_map($this->cartService->toDetails(...), [$cart]);
    }
}
