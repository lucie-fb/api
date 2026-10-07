<?php

namespace App\Service;

use App\Dto\User\UserDetailsOutput;
use App\Dto\User\UserRegisterInput;
use App\Entity\User;
use App\Exception\User\EmailAlreadyUsedException;
use App\Repository\UserRepository;
use App\Service\Utils\AuditService;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AuditService $audit,
        private readonly LoggerInterface $domainLogger,
    ) {
    }

    /**
     * Registers a new account from the submitted payload.
     *
     * @throws EmailAlreadyUsedException when an account already uses this email
     */
    public function register(UserRegisterInput $input): User
    {
        if (null !== $this->userRepository->findOneByEmail($input->email)) {
            // aucune donnée personnelle ici : ni l'adresse refusée, ni le corps reçu
            $this->domainLogger->warning('registration conflict');

            throw new EmailAlreadyUsedException();
        }

        $user = new User();
        $user->setEmail($input->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $input->password));
        $user->setFirstName($input->firstName);
        $user->setLastName($input->lastName);

        $this->audit->stampCreation($user);

        $this->userRepository->persist($user);
        $this->userRepository->flush();

        $this->domainLogger->info('user registered', ['id' => (string) $user->getId()]);

        return $user;
    }

    /**
     * Turns an account into the payload of its detailed representation.
     */
    public function toDetails(User $user): UserDetailsOutput
    {
        return new UserDetailsOutput(
            id: (string) $user->getId(),
            email: $user->getEmail(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            createdAt: $user->getCreatedAt(),
        );
    }
}
