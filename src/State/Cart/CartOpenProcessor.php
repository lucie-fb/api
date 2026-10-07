<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Cart\CartDetailsOutput;
use App\Entity\User;
use App\Service\CartService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProcessorInterface<mixed, CartDetailsOutput>
 */
final class CartOpenProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly CartService $cartService,
    ) {
    }

    /**
     * Serves the pending cart of the authenticated traveller, opening one when there is none.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CartDetailsOutput
    {
        $user = $this->security->getUser();

        // l'opération exige déjà ROLE_USER : ce refus ne sert qu'à ramener le type au User du domaine
        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $this->cartService->toDetails($this->cartService->open($user));
    }
}
