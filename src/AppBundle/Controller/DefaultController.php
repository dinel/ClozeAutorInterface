<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
            case "confirm_age":
                return $this->render('default/confirm_age.html.twig', [ ]);
                
            case "agree_under":
                return $this->render('default/disclaimer.html.twig', [ ]);
                
            case "agree_over":
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
            $session->set("state", "confirm_age");
        } else {
            $session->set("state", "welcome");
        }
        
        return $this->redirectToRoute("homepage");
    }
    
    /**
     * @Route("/confirm_age", name="confirm_age")
     */
    public function confirmAgeAction(Request $request) {
        $session = $request->getSession();
        if($session->get("state") === "welcome") {
            $session->set("state", "confirm_age");
        } else {
            $session->set("state", "welcome");
        }
        
        return $this->redirectToRoute("homepage");
    }
    
    /**
     * @Route("/agree_conditions/{age}", name="agree_conditions")
     */
    public function agreeConditionsAction(Request $request, $age) {
        $session = $request->getSession();
        if($session->get("state") === "confirm_age") {
            $session->set("age_agree", $age);            
            $session->set("state", "agree_" . $age);
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
        if($session->get("state") === "agree_over" || $session->get("state") === "agree_under") {
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
            
            $text = $this->processText($participant->getText());
            $session->set("text", $text);
            
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
     * @Method({"POST"})
     */
    public function thankYouAction(Request $request) {
        $session = $request->getSession();        
        
        if($session->get("state") === "text") {            
            $operations  = $request->request->get("operations");
            $session->set("operations", $operations);
            return $this->render('default/thankyou.html.twig', array(
                        "operations" => $operations,
                    ));
        } else {
            $session->set("state", "welcome");
            return $this->redirectToRoute("homepage");
        }                
    }
    
    /**
     * @Route("/feedback", name="feedback")
     * @Method({"GET", "POST"})
     */
    public function feedbackAction(Request $request) {
        $session = $request->getSession();
        $text = $session->get("text");
        
        if($request->request->get("operations")) {            
            $operations  = $request->request->get("operations");
        } else {            
            $operations = $session->get("operations");
        }
        
        // find offsets
        $counter = 0;
        $offsets = array();
        foreach($text as $block) {
            if($block[0] === 1) {
                $offsets[] = $counter;
            }
            $counter++;
        }
        
        preg_match_all("/:M:word([0-9]+):gap([0-9]+)/", $operations, $matches, PREG_SET_ORDER);
        $filled = array();
        foreach($matches as $val) {
            $filled[(int)$val[2]] = (int)$val[1];
        }
                
        $counter = 0;
        foreach($text as &$block) {
            if($block[0] === 1) {
                if($block[2] === 1) {
                    ;
                } elseif($filled[$counter] === $counter) {
                    $block[2] = 1; //correct
                } else {
                    $block[2] = 2; //incorrect
                    $block[3] = $text[$offsets[$filled[$counter]]][1];
                }
                $counter++;
            }
        }
        
        $session->set("text", $text);                
        
        return $this->render("default/feedback.html.twig", array(
                    "text" => $text,
        ));
    }
    
    /************************************************
     * Private methods
     ************************************************/    
    private function processText($text) {
        /* Structure for each element
         * 0: type 0=text, 1=gap
         * 1: the actual text
         * 2: status 0=not filled, 1=correct, 2=incorrect
         * 3: offset of the filler
         */
        $blocks = array();        
    
        foreach (explode("\n", $text) as $line) {                
            while(preg_match("/^([^\[]*)\[([^\]]+)\](.*)/", $line, $matches)) {
                if(strlen(trim($matches[1])) > 0) {
                    $blocks[] = array(0, trim($matches[1]));
                }
                
                $blocks[] = array(1, $matches[2], 0);
                $line = $matches[3];
            }
            $line .= "<br>";
            $blocks[] = array(0, $line);
        }
        
        return $blocks;
    }
    
}
