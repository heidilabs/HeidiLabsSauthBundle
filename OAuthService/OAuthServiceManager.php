<?php
/**
 * OAuthService Manager
 */

namespace HeidiLabs\SauthBundle\OAuthService;


use HeidiLabs\SauthBundle\Model\OauthServiceInterface;

class OAuthServiceManager
{
    protected $services;

    public function __construct(array $services)
    {
        foreach ($services as $serviceId => $options) {
            $class = $options['class'];
            $config = $options['config'];

            /** @var OauthServiceInterface $service */
            $service = new $class();
            $this->addService($serviceId, $service);
            $service->setup($config);
        }
    }

    /**
     * @param OauthServiceInterface $service
     */
    public function addService($serviceId, OauthServiceInterface $service)
    {
        $this->services[$serviceId] = $service;
    }

    /**
     * @param $key
     * @return OauthServiceInterface The corresponding Service or null if it's not found
     */
    public function getService($key)
    {
        return isset($this->services[$key]) ? $this->services[$key] : null;
    }
}
