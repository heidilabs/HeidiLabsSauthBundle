<?php

namespace HeidiLabs\SauthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleController extends Controller
{
    public function indexAction()
    {
        //redirect to index
        return new RedirectResponse($this->generateUrl('homepage'));
    }
}
