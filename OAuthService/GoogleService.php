<?php
/**
 * Google API Service Provider
 */

namespace HeidiLabs\SauthBundle\OAuthService;

use Google_Service_PlusDomains;
use Google_Client;
use HeidiLabs\SauthBundle\Model\OauthServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleService implements OauthServiceInterface
{
    /** @var  Google_Client */
    protected $googleClient;

    public function setup(array $config)
    {
        $gclient = new Google_Client();
        $gclient->setApplicationName($config['client_name']);
        $gclient->setClientId($config['client_id']);
        $gclient->setClientSecret($config['client_secret']);
        $gclient->addScope(
            [
                Google_Service_PlusDomains::USERINFO_EMAIL
            ]
        );

        $gclient->setRedirectUri($config['callback_url']);

        $this->googleClient = $gclient;
    }

    /**
     * @return mixed
     */
    public function getGoogleClient()
    {
        return $this->googleClient;
    }

    public function getAuthUrl()
    {
        return $this->googleClient->createAuthUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token, UserProviderInterface $userProvider)
    {
        $this->googleClient->authenticate($token->getCredentials());
        $gtoken = json_decode($this->googleClient->getAccessToken());
        $attributes = $this->googleClient->verifyIdToken($gtoken->id_token)->getAttributes();

        return $attributes["payload"]["sub"];
    }

    /**
     * {@inheritdoc}
     * Using E-mail as username
     */
    public function getUsername($id)
    {
        $plus = new \Google_Service_PlusDomains($this->googleClient);
        $user = $plus->people->get($id);

        return $user->getEmails()[0]['value'];
    }
}
