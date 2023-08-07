<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KeycloakController extends AbstractController
{
    public function connect(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('keycloak')
            ->redirect([
                'openid',
                'email',
                'profile',
                'address',
            ]);
    }

    public function verify(Request $request, ClientRegistry $client)
    {
    }
}
