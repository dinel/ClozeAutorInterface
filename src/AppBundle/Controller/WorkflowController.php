<?php

/*
 * Copyright 2017 dinel.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Entity\Participant;
use AppBundle\Form\ParticipantType;

/**
 * Description of WorkflowController
 *
 * @author dinel
 */

class WorkflowController extends Controller 
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $workflow = $request->getSession()->get('workflow');
        if(! $workflow) {
            return $this->redirectToRoute("start");
        }
        
        $state = $workflow[0];
        
        switch ($state) {
            case 'information_sheet':
                return $this->render('default/information_sheet.html.twig', []);
                
            case 'confirm_age':
                return $this->render('default/confirm_age.html.twig', []);
                
            case 'consent_form':
                return $this->render('default/consent_form_' . $request->getSession()->get("age") . 
                        '.html.twig');
                
            case 'questionnaire':
                $participant = new Participant();
                $form = $this->createForm(ParticipantType::class, $participant);
            
                $form->handleRequest($request);
                // form submitted using AJAX request. No need to process submit here
            
                return $this->render('default/questionnaire.html.twig', array(
                    'form' => $form->createView(),
                    'enc_key' => $this->transformMultiLine($this->getFirstSignature()->getKey()),
                ));
                
            case 'thank_you':
                $participant = $this->getDoctrine()
                                    ->getRepository('AppBundle:Participant')
                                    ->find($request->getSession()->get('participantID'));
                $participant->setFinished(1);
                $em = $this->getDoctrine()->getManager();
                $em->persist($participant);
                $em->flush();       
                
                $vouchers = $this->getDoctrine()
                                 ->getRepository('AppBundle:Voucher')
                                 ->createQueryBuilder('v')
                                 ->where('v.available > 0')
                                 ->getQuery()
                                 ->getResult();
                
                return $this->render('default/thankyou.html.twig', array(
                        'participant' => $request->getSession()->get('participantID'),
                        'vouchers' => $vouchers,
                ));
                
            default:
                if(preg_match("/^prereading_([0-9]+)_([0-9]+)$/", $state, $matches)) {
                    $instructions = $this->getInstructions($request->getSession());
                    $quiz = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[1]);
                    
                    $mcq = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[2]);
                    
                    return $this->render('default/display_quiz.html.twig', array(
                            'quiz' => $quiz,
                            'mcq' => $mcq,
                            'instructions' => $instructions,
                    ));
                }
                
                if(preg_match("/^mcq_([0-9]+)$/", $state, $matches)) {  
                    $instructions = $this->getInstructions($request->getSession());
                    $mcq = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[1]);
                    
                    return $this->render('default/display_quiz.html.twig', array(
                            'quiz' => NULL,
                            'mcq' => $mcq,
                            'instructions' => $instructions,
                    ));
                }
                
                if(preg_match("/^cloze_([0-9]+)$/", $state, $matches)) {  
                    $instructions = $this->getInstructions($request->getSession());
                    $cloze = $this->getDoctrine()
                                ->getRepository('AppBundle:ClozeTest')
                                ->find($matches[1]);
                    
                    return $this->render('default/display_cloze.html.twig', array(
                            'cloze' => $cloze,
                            'instructions' => $instructions,
                    ));
                }
                
                if(preg_match("/^cloze_mcq_([0-9]+)$/", $state, $matches)) {  
                    
                    $mcq = $this->getDoctrine()
                                ->getRepository('AppBundle:Quiz')
                                ->find($matches[1]);
                    
                    return $this->render('default/display_cloze_quiz.html.twig', array(
                            'mcq' => $mcq,
                    ));
                }
                
                
                
                if(preg_match("/^subjective_([0-9]+)$/", $state, $matches)) {
                    $instructions = $this->getInstructions($request->getSession());
                    $subjective_survey = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[1]);
                    
                    return $this->render('default/post_test_quiz.html.twig', array(
                            'quiz' => $subjective_survey,
                            'instructions' => $instructions,
                    ));
                }
                
                if(preg_match("/^reviews_([0-9]+)$/", $state, $matches)) {
                    $instructions = $this->getInstructions($request->getSession());
                    $reviews_survey = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[1]);
                    
                    return $this->render('default/post_test_quiz.html.twig', array(
                            'quiz' => $reviews_survey,
                            'instructions' => $instructions,
                    ));
                }
        }
        
        return $this->render('default/information_sheet.html.twig', 
                [
                    'time' => date('l jS \of F Y h:i:s A') . "here",
                    'workflow' => $workflow,
                ]);
    }
    
    /**
     * @Route("/start", name="start")
     */
    public function startAction(Request $request) {
        if(! $this->container->get('session')->isStarted()) {
            $session = new Session();
            $session->start();
        } else {
            $session = $request->getSession();
        }
        $session->invalidate();
        
        $sequences = $this->getTextSelection();
        $session->set('sequence', $sequences[0]);
                
        $workflow = array_merge(
                ['information_sheet', 'confirm_age', 'consent_form', 'questionnaire'],
                $sequences[1], 
                ['subjective_9', 'reviews_10', 'thank_you']);        
        
        $session->set('workflow', $workflow);
        
        $session->set('instruction', 1);
        
        return $this->redirectToRoute("homepage");
    }    
    
    /**
     * @Route("/start-cloze")
     */
    public function startClozeAction(Request $request) {
        if(! $this->container->get('session')->isStarted()) {
            $session = new Session();
            $session->start();
        } else {
            $session = $request->getSession();
        }
        $session->invalidate();
        
        $sequences = $this->getTextSelection();
        $session->set('sequence', $sequences[0]);
                
        $workflow = array_merge(['questionnaire', 'cloze_1', 'cloze_mcq_1']);
                /*
                ['information_sheet', 'confirm_age', 'consent_form', 'questionnaire'],
                $sequences[1], 
                ['subjective_9', 'reviews_10', 'thank_you']);        
                 * 
                 */
        
        $session->set('workflow', $workflow);
        
        $session->set('instruction', 1);
        
        return $this->redirectToRoute("homepage");
    }

    /**
     * @Route("/next", name="next")
     */
    public function nextAction(Request $request) {
        $workflow = $request->getSession()->get('workflow');
        if(! $workflow) {
            return $this->redirectToRoute("start");
        }
        
        $state = array_shift($workflow);
        $request->getSession()->set('workflow', $workflow);
        
        return $this->redirectToRoute("homepage");
    }
    
    /**
     * @Route("/confirm_age/{age}", name="confirm_age")
     */
    public function confirmAgeAction(Request $request, $age) {
        $request->getSession()->set("age", $age);
        return $this->redirectToRoute("next");
    }
    
    /**
     * @Route("/save_details")
     * @Method({"POST"})
     */
    public function saveDetailsAction(Request $request, \Swift_Mailer $mailer) {
        $encrypted  = $request->request->get("encrypted");
        $clear = $request->request->get("clear");
        $participant = new Participant();
        $participant->setSequence($request->getSession()->get('sequence'));
        $participant->setFinished(0);
        $participant->setVoucher("undecided");
        $em = $this->getDoctrine()->getManager();        
        $em->persist($participant);
        $em->flush();                
        
        $id = $participant->getId();
        $request->getSession()->set("participantID", $id);
        
        $message = (new \Swift_Message('Hello Email'))
        ->setFrom('c.orasan@wlv.ac.uk')
        ->setTo($this->getFirstSignature()->getEmail())
        ->setSubject("Participant" . $id)
        ->setBody(
                $clear . "\n" . $encrypted
        );
        $mailer->send($message);                
        
        return $this->redirectToRoute("next");
    }
    
    /**
     * @Route("/log_action")
     * @Method({"POST"})
     */
    public function logAction(Request $request) {
        $timeMiliseconds = $request->request->get("timeMiliseconds");
        $timeClear = $request->request->get("timeClear");
        $message = $request->request->get("message");
        $participant = $request->getSession()->get("participantID");
        
        $log = new \AppBundle\Entity\Log();
        $log->setTimeMiliseconds($timeMiliseconds);
        $log->setTimeClear($timeClear);
        $log->setMessage($message);
        $log->setParticipant($participant);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($log);
        $em->flush();
        
        return new JsonResponse();
    }
    
    /**
     * @Route("/save-voucher")
     */
    public function saveVoucherAction(Request $request) {
        $voucher_code = $request->request->get("voucher");
        $participantID = $request->getSession()->get("participantID");
        $participant = $this->getDoctrine()
                            ->getRepository('AppBundle:Participant')
                            ->find($participantID);
        $participant->setVoucher($voucher_code);
        
        $voucher = $this->getDoctrine()
                        ->getRepository('AppBundle:Voucher')
                        ->findBy(array('code' => $voucher_code))[0];
        $voucher->setAvailable($voucher->getAvailable() - 1);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($participant);
        $em->persist($voucher);
        $em->flush();
        
        return new JsonResponse();
    }
    
    /**
     * @Route("/test-anaphora/{id}")
     * @param type $id the ID of the test to be displayed
     */
    public function displayAnaphoricityTestAction($id) {
        $instructions = $this->getDoctrine()
                             ->getRepository("AppBundle:Instruction")
                             ->find(1);
        
        $test = $this->getDoctrine()
                     ->getRepository('AppBundle:AnaphoricCueingTest')
                     ->find($id);               

        return $this->render('default/display_anaphoric_test.html.twig', [ 
            "text" => $this->processAnaphoricityTest($test->getText()),            
            "title" => $test->getTitle(),
            "instructions" => $instructions,
        ]);                
    }
    

    /**************************************************************
     * 
     * Private methods
     * 
     **************************************************************/
    
    private function getTextSelection() {
        $participants = $this->getDoctrine()
                             ->getRepository("AppBundle:Participant")
                             ->findAll();
        
        $selection = [[1,0], [2,0], [3,0], [4,0]];
        
        foreach($participants as $participant) {
            $seq = explode("-", $participant->getSequence());
            /* P1-P3-M2-M4 */
            foreach($seq as $el) {
                if($el[0] == "P") {
                    $selection[intval(substr($el, 1)) - 1][1]++;
                }
            }            
        }
        srand(time());
        usort($selection, function ($a, $b) {
            if($a[1] < $b[1]) return -1;
            if($a[1] > $b[1]) return 1;
            if($a[1] === $b[1]) {
                return rand() % 2 === 0 ? -1 : 1;
            }
        });
        
        return [
                    "P" . $selection[0][0] . "-" . "P" . $selection[1][0] . "-" .
                    "M" . $selection[2][0] . "-" .  "M" . $selection[3][0],            
                    [
                        'prereading_' . $selection[0][0] . '_' . (intval($selection[0][0]) + 4),
                        'prereading_' . $selection[1][0] . '_' . (intval($selection[1][0]) + 4),
                        'mcq_' . (intval($selection[2][0]) + 4),
                        'mcq_' . (intval($selection[3][0]) + 4),
                    ]           
                ];
    }        
    
    private function getInstructions($session) {
        $id = $session->get('instruction');
        $instruction = $this->getDoctrine()
                            ->getRepository("AppBundle:Instruction")
                            ->find($id);
        $session->set('instruction', $id + 1);
        
        return $instruction;        
    }
    
    /**
     * Function which returns the signature with ID 1 from the database
     * For the moment the functionality is deliberately limited to sending 
     * only one email
     */
    private function getFirstSignature() {       
        $signature = $this->getDoctrine()
                          ->getRepository('AppBundle:Signature')
                          ->find(1);
        
        return $signature;
    }
    
    private function transformMultiLine($string) {
        $result = "";
        
        $lines = explode("\n", $string);
        foreach($lines as $line) {
            $result .= '"' . trim($line) . '\\n" +';
        }
        $result .= substr($result, 0, -2);
        
        return $result;
    }
    
    /**
     * Processes a cloze text
     * @param type $text
     * @return int
     */
    private function processClozeText($text) {
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
    
    /**
     * Processes a cloze text
     * @param type $text
     * @return int
     */
    private function processAnaphoricityTest($text) {
        /* Structure for each element
         * 0: type 0=text, 1=gap, 2=new line
         * 1: the actual text for text, an array with the elements for gap
         * 2: status 0=not filled, 1=correct, 2=incorrect
         * 3: offset of the filler
         */
        $blocks = array();        
    
        foreach (explode("\n", $text) as $line) {                
            while(preg_match("/^([^\[]*)\[([^\]]+)\](.*)/", $line, $matches)) {
                if(strlen(trim($matches[1])) > 0) {
                    $blocks[] = array(0, trim($matches[1]));
                }
                
                $blocks[] = array(1, explode("|", $matches[2]), 0);
                $line = $matches[3];
            }            
            $blocks[] = array(0, $line);
            $blocks[] = array(2);
        }
        
        return $blocks;
    }
}
