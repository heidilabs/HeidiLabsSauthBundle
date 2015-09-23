<?php
/**
 * OAuthService Interface
 */

namespace HeidiLabs\SauthBundle\Model;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface OauthServiceInterface
{
    /**
     * This method should be used to instantiate the clients and define settings like API key
     * @param array $config
     */
    public function setup(array $config);

    /**
     * URL to redirect the user for authentication / authorization
     * @return mixed
     */
    public function getAuthUrl();

    /**
     * Authenticates user via token
     * @param TokenInterface $token
     * @param UserProviderInterface $userProvider
     * @return string The user ID from Google
     */
    public function authenticate(TokenInterface $token, UserProviderInterface $userProvider);

    /**
     * Should return the current user's USERNAME or EMAIL for a query in the database.
     * This is called when there's a new user (new credentials) coming back from authorization and we are trying to
     * match an existing user in the database (the user might have registered through another service
     * or a batch import)
     * @param string $id Remote ID of the user who authorized the app in the OAuth service
     * @return string
     */
    public function getUsername($id);
}
