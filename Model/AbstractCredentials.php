<?php
/**
 * User Credentials - Abstract Superclass
 * Credentials are used for authenticating users
 */

namespace HeidiLabs\SauthBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="serviceId", columns={"serviceName", "serviceId"})})
 */
abstract class AbstractCredentials
{
    /**
     * Credentials Id
     */
    protected $id;

    /**
     * Name of the service in which these credentials are valid. E.g: Google
     * @ORM\Column(type="string", name="serviceName")
     */
    protected $serviceName;

    /**
     * Local user that owns these credentials
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * USER ID in this specific service
     * Will be used to identify the user after authenticating
     * @ORM\Column(type="string", name="serviceId")
     */
    protected $serviceId;

    /**
     * USER SECRET in this specific service
     * Will be used to identify the user after authenticating
     * @ORM\Column(type="text")
     */
    protected $serviceTokens;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param mixed $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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

    /**
     * @return mixed
     */
    public function getServiceTokens()
    {
        return $this->serviceTokens;
    }

    /**
     * @param mixed $serviceTokens
     */
    public function setServiceTokens($serviceTokens)
    {
        $this->serviceTokens = $serviceTokens;
    }
}
