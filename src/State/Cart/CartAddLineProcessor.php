<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Cart\CartAddLineInput;
use App\Dto\Cart\CartDetailsOutput;
use App\Entity\User;
use App\Service\CartService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProcessorInterface<CartAddLineInput, CartDetailsOutput>
 */
final readonly class CartAddLineProcessor implements ProcessorInterface
{
    public function __construct(
        private Security    $security,
        private CartService $cartService,
    ) {
    }

    /**
     * Serves the pending cart of the authenticated traveller, opening one when there is none.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CartDetailsOutput
    {
        $cart = $this->cartService->findOneById($uriVariables['id']);

        return $this->cartService->toDetails($this->cartService->open($cart));
    }
}
