<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        if(! $this->container->get('session')->isStarted()) {
            $session = new Session();
            $session->start();
            $session->set("state", "welcome");
        } else {        
            $session = $request->getSession();
        }
            
        switch ($session->get("state")) {                
            case "disclaimer":
                $session->set("state", "survey");
                return $this->render('default/disclaimer.html.twig', [ ]);
                
            case "survey":
                $session->set("state", "text");
                return $this->render('default/survey.html.twig', [ ]);
                
            case "text":
                $session->set("state", "thankyou");
                $text = explode(" ", "This is a test [string] that will be [displayed] with gaps");
                return $this->render('default/text.html.twig', [ 
                    "text" => $text,
                ]);
                
            case "thankyou":
                $session->invalidate();
                return $this->render('default/thankyou.html.twig', [ ]);                
                
            default:
                $session->set("state", "disclaimer");
                return $this->render('default/index.html.twig', [ ]);
        }        
    }           
}
