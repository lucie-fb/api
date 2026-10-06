<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Cart\CartDetailsOutput;
use App\Entity\User;
use App\Service\CartService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }
        return $this->cartService->toDetails($this->cartService->open($user));
    }
}
