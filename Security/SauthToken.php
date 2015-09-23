<?php
/**
 * Custom Sauth Token
 */

namespace HeidiLabs\SauthBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class SauthToken extends PreAuthenticatedToken
{
    protected $serviceId;

    /**
     * @param array|\Symfony\Component\Security\Core\Role\RoleInterface[] $user
     * @param $credentials
     * @param $providerKey
     * @param array $roles
     */
    public function __construct($user, $credentials, $providerKey, array $roles = array())
    {
        parent::__construct($user, $credentials, $providerKey, $roles);
    }

    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param mixed $serviceId
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
    }
}
