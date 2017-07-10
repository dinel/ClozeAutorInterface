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
            $session->set("text", $participant->getText());
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
        $text = explode(" ", str_replace("\n", "<br>", $session->get("text")));
        
        if($request->request->get("operations")) {            
            $operations  = $request->request->get("operations");
            echo "POST:" . $operations . "<br>";
        } else {            
            $operations = $session->get("operations");
            echo "SESSION" . $operations . "<br>";
        }
        
        print_r($text);
        echo("<br><br>");
        
        // collect the gaps
        $gaps = array();
        foreach($text as $word) {
            if(strlen(trim($word)) === 0) {
                continue;
            }
                
            if($word[0] == "[") {
                $gaps[] = substr($word, 1, strlen($word) - 2);
            }
        }
        
        print_r($gaps);
        echo("<br><br>");
        
        preg_match_all("/:M:word([0-9]+):gap([0-9]+)/", $operations, $matches, PREG_SET_ORDER);
        $filled = array();
        foreach($matches as $val) {
            $filled[(int)$val[2]] = (int)$val[1];
        }
        
        print_r($filled);
        echo("<br><br>");
        
        // recreate the text
        $the_text = "";
        $the_correct_text = "";
        $counter = 0;
        foreach($text as $word) {
            if(strlen(trim($word)) === 0) {
                continue;
            }
                
            if($word[0] == "[") {
                if($filled[$counter] === $counter) {
                    $the_text .= (" [" . substr($word, 1, strlen($word) - 2) . "] ");
                    $the_correct_text .= (" " . substr($word, 1, strlen($word) - 2) . " ");
                } else {
                    $the_text .= (" [" . $gaps[$filled[$counter]] . "] ");
                    $the_correct_text .= (" " . $word . " ");
                }
                $counter++;
            } else {
                $the_text .= (" " . $word . " ");
                $the_correct_text .= (" " . $word . " ");
            }
        }
        $session->set("text", preg_replace('/\s+/', ' ', $the_correct_text));
        $text_new = explode(" ", str_replace("\n", "<br>", 
                preg_replace('/\s+/', ' ', $the_text)));
        
        print_r($text_new);
        echo("<br><br>");
        
        print_r($the_correct_text);
        echo("<br><br>");
        
        return $this->render("default/feedback.html.twig", array(
                    "filled" => $filled,
                    "text" => $text_new,
                    //"the_correct_text" => explode(" ", str_replace("\n", "<br>", $the_correct_text)
                    "the_correct_text" => $text,
        ));
    }
}
