<?php

namespace HeidiLabs\SauthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction($service)
    {
        //redirect to index
        return new RedirectResponse($this->generateUrl($this->getParameter('sauth.home')));
    }
}
