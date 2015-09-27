<?php
/**
 * Sauth Authenticator
 */

namespace HeidiLabs\SauthBundle\Security;

use HeidiLabs\SauthBundle\Exception\ServiceNotFoundException;
use HeidiLabs\SauthBundle\Model\AbstractUser;
use HeidiLabs\SauthBundle\OAuthService\OAuthServiceManager;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class SauthAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    protected $oauth;
    protected $allowRegistration;

    /**
     * @param OAuthServiceManager $oauth
     * @param bool $allowRegistration When set to true, a new user will be created when
     *                                there isn't a match for the credentials obtained
     */
    public function __construct(OAuthServiceManager $oauth, $allowRegistration = true)
    {
        $this->oauth = $oauth;
        $this->allowRegistration = $allowRegistration;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(Request $request, $providerKey)
    {
        $serviceId = $this->getServiceIdFromUrl($request->getPathInfo());

        if (!$serviceId) {
            return;
        }

        $code = $request->query->get('code');

        if (!$code) {
            throw new BadCredentialsException('No OAuth code found');
        }

        $token = new SauthToken(
            'anon.',
            $code,
            $providerKey
        );
        $token->setServiceId($serviceId);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof UserManager) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of UserManager (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $user = $token->getUser();
        if ($user instanceof AbstractUser) {
            return new SauthToken(
                $user,
                $token->getCredentials(),
                $providerKey,
                $user->getRoles()
            );
        }

        $service = $this->oauth->getService($token->getServiceId());

        $userId = $service->authenticate($token, $userProvider);

        if (!$userId) {
            throw new AuthenticationException(
                sprintf('Authentication Problem.')
            );
        }

        $credentials = $userProvider->getCredentials($token->getServiceId(), $userId);

        if ($credentials) {
            $user = $credentials->getUser();
        } else {
            $username = $service->getUsername($userId);
            $user = $userProvider->loadUserByUsername($username);

            if (!$user) {
                if ($this->allowRegistration === false) {
                    throw new AccessDeniedException(
                        "We couldn't find a user matching these credentials. New registrations are currently closed."
                    );
                }

                $user = $userProvider->createNew($username);
            }

            $userProvider->saveCredentials($user, $token->getServiceId(), $userId, $service->getUserTokens());
        }

        return new SauthToken(
            $user,
            $token->getCredentials(),
            $providerKey,
            $user->getRoles()
        );
    }


    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $serviceId = $this->getServiceIdFromUrl($request->getPathInfo());

        if (!$serviceId) {
            throw new ServiceNotFoundException('OAuth Service not found.');
        }

        return new RedirectResponse($this->oauth->getService($serviceId)->getAuthUrl());
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function getServiceIdFromUrl($path)
    {
        $matches = [];
        if (!preg_match("^connect/(.*)^", $path, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
