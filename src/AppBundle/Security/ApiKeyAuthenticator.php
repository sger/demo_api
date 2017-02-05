<?php

// src/AppBundle/Security/ApiKeyAuthenticator.php
namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
      * Called on every request returns api key.
      *
      * @param   Symfony\Component\HttpFoundation\Request
      * @return  string
      */
    public function getCredentials(Request $request)
    {
        // Check if token exists in http header.
        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            return;
        }

        // Matching the id with token we need to find the correct user.
        if ($id = $request->get('id')) {
            $user = $this->em->getRepository('AppBundle:User')->getAuthUserForApiKey($token, $id);

            if (!$user) {
              $logger = $this->container->get('monolog.logger.ERROR_LOG_CHANNEL');
              $logger->error('getCredentials', [
                'field1' => 'test1',
                'field2' => 'test2',
                'field3' => 'test3',
              ]);

              throw new AuthenticationCredentialsNotFoundException();
            }
        }

        return array(
            'token' => $token,
        );
    }

    /**
     * Get information about user.
     *
     * @param   string
     * @param   Symfony\Component\Security\Core\User\UserProviderInterface
     * @return  AppBundle\Entity\User
     *
     * @throws Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->em->getRepository('AppBundle:User')->findOneBy(array('apiKey' => $credentials));

        if (!$user) {
            $logger = $this->container->get('monolog.logger.ERROR_LOG_CHANNEL');
            $logger->error('getCredentials', [
              'field1' => 'test1',
              'field2' => 'test2',
              'field3' => 'test3',
            ]);

            throw new AuthenticationCredentialsNotFoundException();
        }

        return $user;
    }

    /**
     * Check credentials - e.g. make sure the password is valid no credential check is
     * needed in this case return true to cause authentication success.
     *
     * @param   string
     * @param   Symfony\Component\Security\Core\User\UserInterface
     * @return  boolean
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $logger = $this->container->get('monolog.logger.SUCCESS_LOG_CHANNEL');
        $logger->info('onAuthenticationSuccess', [
          'field1' => 'test1',
          'field2' => 'test2',
          'field3' => 'test3',
        ]);

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );

        $logger = $this->container->get('monolog.logger.ERROR_LOG_CHANNEL');
        $logger->error('onAuthenticationFailure', [
          'field1' => 'test1',
          'field2' => 'test2',
          'field3' => 'test3',
        ]);

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
