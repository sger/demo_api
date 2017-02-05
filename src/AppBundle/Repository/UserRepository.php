<?php

namespace AppBundle\Repository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param string $username
     * @return User
     */
    public function findByUsernameOrEmail($username)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getUserForApiKey($apiKey)
    {
       return $this->createQueryBuilder('u')
           ->andWhere('u.apiKey = :apiKey')
           ->setParameter('apiKey', $apiKey)
           ->getQuery()
           ->getOneOrNullResult();
    }

    public function getAuthUserForApiKey($apiKey, $user)
    {
       return $this->createQueryBuilder('u')
           ->andWhere('u.apiKey = :apiKey AND u.id = :id')
           ->setParameter('apiKey', $apiKey)
           ->setParameter('id', $user)
           ->getQuery()
           ->getOneOrNullResult();
    }
}