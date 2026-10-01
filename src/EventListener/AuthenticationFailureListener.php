<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_failure')]
final class AuthenticationFailureListener
{
    public function __invoke(AuthenticationFailureEvent $event): void
    {
        $response = new JsonResponse([
            'type' => 'about:blank',
            'title' => 'Identifiants invalides',
            'status' => Response::HTTP_UNAUTHORIZED,
            'detail' => 'Adresse e-mail ou mot de passe incorrect.',
        ], Response::HTTP_UNAUTHORIZED);

        $response->headers->set('Content-Type', 'application/problem+json');

        $event->setResponse($response);
    }
}
