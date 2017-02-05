<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/logger")
     */
    public function loggerAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $logger = $this->container->get('monolog.logger.ERROR_LOG_CHANNEL');

        $logger->info('I just got the logger !!!! update', array(
          'field1' => 'test1',
          'field2' => 'test2',
          'field3' => 'test3',
        ));

        return new JsonResponse(array('result' => 'Pass authentication!'));
    }

    /**
     * @Route("/user/{id}")
     */
    public function getUserAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($id);

        if (!$id) {
            throw new HttpException(400, "Invalid id");
        }

        $logger = $this->container->get('monolog.logger.SUCCESS_LOG_CHANNEL');
        
        $logger->info('getUserAction with id '.$id, [
          'field1' => 'test1',
          'field2' => 'test2',
          'field3' => 'test3',
        ]);

        return new JsonResponse(array('user' => $user->username));
    }
}
