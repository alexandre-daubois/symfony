<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Decorates the original Authenticator class to log information
 * about the security authenticators and the decisions made by them.
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @internal
 */
class TraceableAuthenticatorManager implements AuthenticatorManagerInterface
{
    private $manager;
    private $authenticators = [];
    private $authenticatorsLog = []; // All authentication logs

    public function __construct(AuthenticatorManagerInterface $manager)
    {
        $this->manager = $manager;

        if ($this->manager instanceof AuthenticatorManager) {
            // The strategy and voters are stored in a private properties of the decorated service
            $reflection = new \ReflectionProperty(AuthenticatorManager::class, 'authenticators');
            $reflection->setAccessible(true);
            $this->authenticators = $reflection->getValue($manager);
        }
    }

    public function supports(Request $request): ?bool
    {
        foreach ($this->authenticators as $authenticator) {
            $this->authenticatorsLog[\get_class($authenticator)] = [
                'authenticator' => $authenticator,
                'supports' => $authenticator->supports($request),
                'response' => null,
            ];
        }

        return $this->manager->supports($request);
    }

    public function authenticateRequest(Request $request): ?Response
    {
        foreach ($this->authenticators as $authenticator) {
            $request->attributes->set('_security_authenticators', [$authenticator]);
            $response = $this->manager->authenticateRequest($request);

            $this->authenticatorsLog[\get_class($authenticator)]['response'] = $response;

            if (null !== $response) {
                return $response;
            }
        }

        return null;
    }

    public function getAuthenticators(): array
    {
        return $this->authenticators;
    }

    public function getAuthenticatorsLog(): array
    {
        return $this->authenticatorsLog;
    }
}

class_alias(TraceableAuthenticatorManager::class, DebugAuthenticatorManager::class);
