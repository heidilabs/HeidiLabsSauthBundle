<?php
/**
 * User - Abstract Superclass
 */

namespace HeidiLabs\SauthBundle\Model;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\MappedSuperclass */
class AbstractUser implements UserInterface, \Serializable
{
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * This will be used as unique identifier for users coming from OAuth.
     * Email will be used in this field when username is not available
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="simple_array")
     */
    protected $roles = [];

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->getId(),
            $this->getUsername(),
            $this->getPassword(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     *
     * @return array The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->credentials = null;
    }
}
