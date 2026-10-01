<?php

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\User\UserDetailsOutput;
use App\Dto\User\UserRegisterInput;
use App\Service\UserService;

/**
 * @implements ProcessorInterface<UserRegisterInput, UserDetailsOutput>
 */
final class UserRegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    /**
     * Registers the submitted account and returns its detailed representation.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserDetailsOutput
    {
        return $this->userService->toDetails($this->userService->register($data));
    }
}
