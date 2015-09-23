<?php
/**
 * Sauth User Manager / Provider
 */

namespace HeidiLabs\SauthBundle\Security;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use HeidiLabs\SauthBundle\Exception\InvalidEntityException;
use HeidiLabs\SauthBundle\Model\AbstractCredentials;
use HeidiLabs\SauthBundle\Model\AbstractUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserManager implements UserProviderInterface
{
    protected $doctrine;
    protected $userClass;
    protected $credentialsClass;

    /**
     * @param Registry $doctrine
     * @param string $userClass User Entity Class. Default config value is AppBundle\Entity\User
     * @param string $credentialsClass Credentials Entity Class. Default config value is AppBundle\Entity\Credentials
     *
     * @throws InvalidEntityException
     */
    public function __construct(Registry $doctrine, $userClass, $credentialsClass)
    {
        $this->doctrine = $doctrine;

        $userTest = new $userClass();
        $credentialsTest = new $credentialsClass();

        if (! ($userTest instanceof AbstractUser)) {
            throw new InvalidEntityException(
                sprintf('The User Entity "%s" is invalid or not supported by SauthBundle.', $userClass)
            );
        }

        if (! ($credentialsTest instanceof AbstractCredentials)) {
            throw new InvalidEntityException(
                sprintf('The Credentials Entity "%s" is invalid or not supported by SauthBundle.', $credentialsClass)
            );
        }

        $this->userClass = $userClass;
        $this->credentialsClass = $credentialsClass;
    }

    /**
     * @param string $serviceName
     * @param string $serviceId
     * @return AbstractCredentials|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCredentials($serviceName, $serviceId)
    {
        /** @var EntityManager $doctrine */
        $doctrine = $this->doctrine->getManager();

        $qb = $doctrine->createQueryBuilder()
            ->select('c')
            ->from($this->credentialsClass, 'c');

        return $qb->where($qb->expr()->andX(
            $qb->expr()->eq('c.serviceName', '?1'),
            $qb->expr()->eq('c.serviceId', '?2')
        ))
            ->setParameter(1, $serviceName)
            ->setParameter(2, $serviceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param AbstractUser $user
     * @param $serviceName
     * @param $serviceId
     * @param $serviceTokens
     */
    public function saveCredentials(AbstractUser $user, $serviceName, $serviceId, $serviceTokens)
    {
        /** @var EntityManager $doctrine */
        $doctrine = $this->doctrine->getManager();

        $credentials = new $this->credentialsClass();
        $credentials->setServiceName($serviceName);
        $credentials->setServiceId($serviceId);
        $credentials->setServiceTokens(serialize($serviceTokens));
        $credentials->setUser($user);

        $doctrine->persist($credentials);
        $doctrine->flush();
    }

    /**
     * @param string $username
     * @return AbstractUser $user
     */
    public function createNew($username)
    {
        /** @var AbstractUser $user */
        $user = new $this->userClass();

        $user->setUsername($username);
        $user->setRoles(['ROLE_USER']);
        $this->saveUser($user);

        return $user;
    }

    /**
     * @param AbstractUser $user
     */
    public function saveUser(AbstractUser $user)
    {
        /** @var EntityManager $doctrine */
        $doctrine = $this->doctrine->getManager();

        $doctrine->persist($user);
        $doctrine->flush();
    }

    /**
     * {@inheritdoc}
     * @return AbstractUser $user
     */
    public function loadUserByUsername($username)
    {
        /** @var EntityManager $doctrine */
        $doctrine = $this->doctrine->getManager();

        return $doctrine->getRepository($this->userClass)->findOneBy(['username' => $username]);
    }

    /**
     * @param string $email
     * @return AbstractUser $user
     */
    public function loadUserByEmail($email)
    {
        /** @var EntityManager $doctrine */
        $doctrine = $this->doctrine->getManager();

        return $doctrine->getRepository($this->userClass)->findOneBy(['email' => $email]);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        /** @var EntityManager $doctrine */
        $doctrine = $this->doctrine->getManager();

        return $doctrine->getRepository($this->userClass)->findOneById($user->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $this->userClass === $class;
    }
}
