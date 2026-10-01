<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_success', priority: -10)]
final class AuthenticationSuccessListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();

        $aligned = ['accessToken' => $data['token']];

        // le bundle de renouvellement écoute le même événement et a déjà posé sa clé,
        // sans distinguer la connexion du renouvellement : on la reprend ou on la laisse tomber
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->getPathInfo() === '/api/auth/login' && isset($data['refreshToken'])) {
            $aligned['refreshToken'] = $data['refreshToken'];
        }

        $event->setData($aligned);
    }
}
