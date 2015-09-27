<?php
/**
 * Github API Service Provider
 */

namespace HeidiLabs\SauthBundle\OAuthService;

use GuzzleHttp\Client;
use HeidiLabs\SauthBundle\Exception\UnauthorizedException;
use HeidiLabs\SauthBundle\Model\OauthServiceInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GithubService implements OauthServiceInterface
{
    /** @var  string */
    protected $clientId;

    /** @var  string */
    protected $clientSecret;

    /** @var  string */
    protected $callbackUrl;

    /** @var  Client $guzzle */
    protected $guzzle;

    /** @var  SessionInterface $session */
    protected $session;

    /** @var  string */
    protected $accessToken;

    /** @var  mixed json decoded object */
    protected $loggedUser;

    const AUTH_URL = 'https://github.com/login/oauth/authorize';
    const AUTH_TOKEN_URL = 'https://github.com/login/oauth/access_token';
    const API_BASE = 'https://api.github.com';
    const AUTH_STATE_KEY = 'github_oauth_state';

    public function setup(array $config, SessionInterface $session)
    {
        $this->session = $session;
        $this->guzzle = new Client();
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->callbackUrl = $config['callback_url'];
    }

    public function getAuthUrl()
    {
        $state = substr(md5(time()), 0, 15);
        $this->session->set(self::AUTH_STATE_KEY, $state);

        $parameters = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->callbackUrl,
            'state'        => $state
        ];

        return self::AUTH_URL . '?' . http_build_query($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token, UserProviderInterface $userProvider)
    {
        //POST https://github.com/login/oauth/access_token
        $code = $token->getCredentials();

        $response = $this->guzzle->post(self::AUTH_TOKEN_URL, [
            'headers' => [
                'Accept' => 'application/json'
            ],
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'state' => $this->session->get(self::AUTH_STATE_KEY)
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $content = json_decode($response->getBody());

            $this->accessToken = $content->access_token;
            $this->loggedUser = $this->getLoggedUser();

            return $this->loggedUser->id;
        }

        $this->session->set(self::AUTH_STATE_KEY, null);

        return null;
    }

    /**
     * {@inheritdoc}
     * Using E-mail as username
     */
    public function getUsername($id)
    {
        return $this->loggedUser->login;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserTokens()
    {
        return $this->accessToken;
    }

    public function getLoggedUser()
    {
        return $this->get('/user');
    }

    public function get($endpoint, $parameters = [])
    {
        if (!$this->accessToken) {
            throw new UnauthorizedException("User not authenticated.");
        }

        $response = $this->guzzle->get(self::API_BASE . $endpoint, [
            'headers' => [
                'Authorization' => 'token ' . $this->accessToken
            ],
            'query' => $parameters
        ]);

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody());
        }

        return null;
    }
}
