<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        } else {        
            $session = $request->getSession();
        }
        
        if(! $session->has("state")) {
            $session->set("state", "welcome");
        }
            
        switch ($session->get("state")) {                
            case "disclaimer":
                return $this->render('default/disclaimer.html.twig', [ ]);
                
            case "survey":                
                return $this->render('default/survey.html.twig', [ ]);
                
            case "thankyou":
                
                return $this->render('default/thankyou.html.twig', [ ]);                
                
            default:                
                return $this->render('default/index.html.twig', [ ]);
        }        
    }
    
    /**
     * @Route("/confirm_interest", name="confirm_interest")
     */
    public function confirmInterestAction(Request $request) {
        $session = $request->getSession();
        if($session->get("state") === "welcome") {
            $session->set("state", "disclaimer");
        } else {
            $session->set("state", "welcome");
        }
        
        return $this->redirectToRoute("homepage");
    }
    
    /**
     * @Route("/confirm_participation", name="confirm_participation")
     */
    public function confirmParticipationAction(Request $request) {
        $session = $request->getSession();
        if($session->get("state") === "disclaimer") {
            $session->set("state", "survey");
            return $this->redirectToRoute("questionnaire");
        } else {
            $session->set("state", "welcome");
            return $this->redirectToRoute("homepage");
        }                
    }    
    
    /**
     * @Route("/questionnaire", name="questionnaire")
     */
    public function questionnaireAction(Request $request) {
        $session = $request->getSession();
        if($session->get("state") === "survey") {
            $participant = new \AppBundle\Entity\Participant();
            $form = $this->createFormBuilder($participant)
                    ->add('text', TextareaType::class, array(
                        'attr' => array('cols' => '100', 
                            'rows' => 10)
                    ))
                    ->add('save', SubmitType::class)
                    ->getForm();
            
            $form->handleRequest($request);
            
            if($form->isSubmitted() && $form->isValid()) {
                $session->set("state", "text");                
                $participant = $form->getData();
                $session->set("participant", $participant);
                
                return $this->redirectToRoute("display_text");
            }
            
            return $this->render('default/survey.html.twig', array(
                'form' => $form->createView(),
            ));                        
        } else {
            $session->set("state", "welcome");
            return $this->redirectToRoute("homepage");
        }
    }
    
    /**
     * @Route("/text", name="display_text")
     */
    public function displayTextAction(Request $request) {
        $session = $request->getSession();
        if($session->get("state") === "text") {
            
            $participant = $session->get("participant");
            $text = explode(" ", str_replace("\n", "<br>", $participant->getText()));
            return $this->render('default/text.html.twig', [ 
                "text" => $text,
            ]);
        } else {
            $session->set("state", "welcome");
            return $this->redirectToRoute("homepage");
        }                
    }
    
    /**
     * @Route("/thank-you", name="thank_you")
     */
    public function thankYouAction(Request $request) {
        $session = $request->getSession();        
        
        if($session->get("state") === "text") {
            $session->invalidate();
            return $this->render('default/thankyou.html.twig');
        } else {
            $session->set("state", "welcome");
            return $this->redirectToRoute("homepage");
        }                
    }
}
