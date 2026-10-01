<?php

namespace App\Service;

use App\Dto\User\UserDetailsOutput;
use App\Dto\User\UserRegisterInput;
use App\Entity\User;
use App\Exception\User\EmailAlreadyUsedException;
use App\Repository\UserRepository;
use App\Service\Utils\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly AuditService $audit,
        private readonly UserRepository $users,
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
        if (null !== $this->users->findOneByEmail($input->email)) {
            // aucune donnée personnelle ici : ni l'adresse refusée, ni le corps reçu
            $this->domainLogger->warning('registration conflict');

            throw new EmailAlreadyUsedException();
        }

        $user = new User();
        $user->setEmail($input->email);
        $user->setPassword($this->hasher->hashPassword($user, $input->password));
        $user->setFirstName($input->firstName);
        $user->setLastName($input->lastName);

        $this->audit->stampCreation($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->domainLogger->info('user registered', ['id' => (string) $user->getId()]);

        return $user;
    }

    /**
     * Turns an account into the payload of its detailed representation.
     */
    public function toDetails(User $user): UserDetailsOutput
    {
        return new UserDetailsOutput(
            (string) $user->getId(),
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getCreatedAt(),
        );
    }
}
