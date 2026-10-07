<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Cart\CartAddLineInput;
use App\Dto\Cart\CartDetailsOutput;
use App\Service\CartService;

/**
 * @implements ProcessorInterface<CartAddLineInput, CartDetailsOutput>
 */
final class CartAddLineProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    /**
     * Adds the submitted line to the cart carried by the URL, and serves the cart it belongs to.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CartDetailsOutput
    {
        // $data porte l'objet d'entrée désérialisé, pas le panier : celui-ci se retrouve par l'URL,
        // du même geste que le provider. `previous_data` décrit l'état d'une ressource avant
        // modification, sur une mise à jour : s'y fier depuis un Post n'est promis par rien.
        $cart = $this->cartService->findOneById($uriVariables['id']);

        return $this->cartService->toDetails($this->cartService->addLine($cart, $data));
    }
}
