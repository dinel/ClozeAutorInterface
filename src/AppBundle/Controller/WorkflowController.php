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
                return $this->render('default/information_sheet.html.twig', [
                    'type_is' => $request->getSession()->get('type'),
                ]);
                
            case 'confirm_age':
                return $this->render('default/confirm_age.html.twig', []);
                
            case 'consent_form':
                return $this->render('default/consent_form_' . $request->getSession()->get("age") . '.html.twig', [
                    'type_is' => $request->getSession()->get('type'),
                ]);
                
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
                
            case 'thank_you_resume':
                return $this->render('default/thankyou-simple.html.twig');
                
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
                    $cloze = $this->getDoctrine()
                                ->getRepository('AppBundle:ClozeTest')
                                ->find($matches[1]);
                    
                    return $this->render('default/display_cloze_test.html.twig', array(
                            'text' => $this->processClozeText($cloze->getText()),
                            'title' => $cloze->getTitle(),
                            'textID' => $cloze->getId(),
                    ));
                }
                
                if(preg_match("/^anaphora_([0-9]+)$/", $state, $matches)) {  
                    $ancue = $this->getDoctrine()
                                  ->getRepository('AppBundle:AnaphoricCueingTest')
                                  ->find($matches[1]);
                    
                    return $this->render('default/display_anaphoric_test.html.twig', array(
                            'text' => $this->processAnaphoricityTest($ancue->getText()),
                            'title' => $ancue->getTitle(),
                            'textID' => $ancue->getId(),
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
                
                if(preg_match("/^cloze_mcq_baseline_([0-9]+)$/", $state, $matches)) {                      
                    $mcq = $this->getDoctrine()
                                ->getRepository('AppBundle:Quiz')
                                ->find($matches[1]);
                    
                    return $this->render('default/display_quiz_baseline.html.twig', array(
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
                
                if(preg_match("/^instructions_([0-9]+)$/", $state, $matches)) {
                    $instructions = $this->getDoctrine()
                                         ->getRepository('AppBundle:Instruction')
                                         ->find($matches[1]);
                    
                    return $this->render('default/display_instructions.html.twig', array(
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
        
        return $this->render('default/information_sheet_1.html.twig', 
                [
                    'time' => date('l jS \of F Y h:i:s A') . "here",
                    'workflow' => $workflow,
                ]);
    }
    
    /**
     * @Route("/start-prereading", name="start")
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
        $session->set('type', "prereading");
                
        $workflow = array_merge(
                ['information_sheet', 'confirm_age', 'consent_form', 'questionnaire'],
                $sequences[1], 
                ['subjective_9', 'reviews_10', 'thank_you']);        
        
        $session->set('workflow', $workflow);
        
        // ugly hack to avoid refresh problem
        $session->set('instruction', -3);
        
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
        
        $sequences = $this->getClozeSelection();
        $session->set('sequence', $sequences[0]);
        $session->set('type', "cloze");
        
        
        $workflow = array_merge(
                ['information_sheet', 'confirm_age', 'consent_form', 'questionnaire', 'instructions_11'],
                $sequences[1], 
                ['subjective_15', 'thank_you']);        
        
        $session->set('workflow', $workflow);
        
        $session->set('instruction', 12);
        
        return $this->redirectToRoute("homepage");
    }
    
    /**
     * @Route("/resume/{id}")
     */
    public function resumeAction(Request $request, $id) {
        $resume = $this->getDoctrine()
                       ->getRepository("AppBundle:Resume")
                       ->find($id);
        
        if($resume && !$resume->getUsed()) {
            $workflow = explode(",", $resume->getSequence());
            //['mcq_5', 'subjective_9', 'reviews_10'];

            $session = $request->getSession();
            $session->invalidate();

            $session->set('workflow', $workflow);       
            $session->set('instruction', $resume->getInstructions());
            $session->set("participantID", $resume->getParticipant());
            
            $em = $this->getDoctrine()->getManager();        
            $resume->setUsed(1);
            $em->persist($resume);
            $em->flush();  

            return $this->redirectToRoute("homepage");
        } else {
            return $this->render("default/thankyou-simple.html.twig");
        }
    }

    /**
     * @Route("/next", name="next")
     */
    public function nextAction(Request $request) {
        $workflow = $request->getSession()->get('workflow');
        if(! $workflow) {
            return $this->redirectToRoute("start");
        }
        
        $id = $request->getSession()->get('instruction');
        if($id !== 12) {
            $request->getSession()->set('instruction', $id + 1);
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
        ->setSubject("Participant" . $id . ' (' 
                . $request->getSession()->get('type') . ')')
        ->setBody(
                $clear . "\n" . $encrypted
        );
        $mailer->send($message);                
        
        return $this->redirectToRoute("next");
    }
    
    /**
     * @Route("/get-voucher-report")
     */
    public function getVoucherReportAction(Request $request, \Swift_Mailer $mailer) {
        $participants = $this->getDoctrine()
                             ->getRepository('AppBundle:Participant')
                             ->findAll();
            
        return $this->render('default/vouchers.html.twig', [
            'participants' => $participants,            
        ]);              
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
     * @Route("/save-contact")
     */
    public function saveContactAction(Request $request) {
        $contact = $request->request->get("value");
        $participantID = $request->getSession()->get("participantID");
        $participant = $this->getDoctrine()
                            ->getRepository('AppBundle:Participant')
                            ->find($participantID);
        $participant->setFutureExperiment($contact);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($participant);
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
            // check if the participant finished
            if($participant->getFinished() == 0) {
                continue;
            }
            
            // check if it is the correct experiment
            if($participant->getSequence()[0] != "P" && $participant->getSequence()[0] != "M") {
                continue;
            }
            
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

    private function getClozeSelection() {
        $participants = $this->getDoctrine()
                             ->getRepository("AppBundle:Participant")
                             ->findAll();
        
        $distribution = array();
        $distribution["A"] = [0, 0, 0, 0];
        $distribution["B"] = [0, 0, 0, 0];
        $distribution["C"] = [0, 0, 0, 0];
        $distribution["D"] = [0, 0, 0, 0];
        
        foreach($participants as $participant) {
            // check if the participant finished
            if($participant->getFinished() == 0) {
                continue;
            }
            
            // check if it is the correct experiment
            if($participant->getSequence()[0] == "P" || $participant->getSequence()[0] == "M") {
                continue;
            }
            
            $seq = explode("-", $participant->getSequence());
            /* P1-P3-M2-M4 */
            foreach($seq as $el) {
                $distribution[$el[0]][intval($el[1]) - 1]++;
            }            
        }
        
        $new_experiment = array();
        $selected = array();
        
        foreach($distribution as $key => $value) {
            $el = $this->getLeast($distribution[$key], $selected);
            $selected[] = $el;
            $new_experiment[] = $key . $el;
        }
                        
        shuffle($new_experiment);
        return [implode("-", $new_experiment), $this->produceMappings($new_experiment)];
    }  
    
    private function produceMappings($experiment) {
        $res = [];
        
        foreach($experiment as $step) {
            if($step[0] == "A") {
                $res[] = "instructions_7";
                $res[] = "cloze_mcq_baseline_" . strval(10 + intval($step[1]));
            }
            
            if($step[0] == "B") {
                $res[] = "instructions_8";
                $res[] = "cloze_" . strval(6 + intval($step[1]));
                $res[] = "cloze_mcq_" . strval(10 + intval($step[1]));
            }
            
            if($step[0] == "C") {
                $res[] = "instructions_9";
                $res[] = "cloze_" . strval(10 + intval($step[1]));
                $res[] = "cloze_mcq_" . strval(10 + intval($step[1]));
            }
            
            if($step[0] == "D") {
                $res[] = "instructions_10";
                $res[] = "anaphora_" . strval(4 + intval($step[1]));
                $res[] = "cloze_mcq_" . strval(10 + intval($step[1]));
            }
        }
        
        return $res;
    }

    private function getLeast($dist, $selected) {
        $pairs = array();
        for($i = 0; $i < count($dist); $i++) {
            if(! in_array(strval($i + 1), $selected)) {
                $pairs[] = array($dist[$i], $i + 1);
            }
        }
        
        srand(time());
        usort($pairs, function ($a, $b) {
            if($a[0] < $b[0]) return -1;
            if($a[0] > $b[0]) return 1;
            if($a[0] === $b[0]) {
                return rand() % 2 === 0 ? -1 : 1;
            }
        });
        
        return strval($pairs[0][1]);
    }


    private function getInstructions($session) {
        $id = $session->get('instruction');
        $instruction = $this->getDoctrine()
                            ->getRepository("AppBundle:Instruction")
                            ->find($id);        
        
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
         * 0: type 0=text, 1=gap, 2=new line
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
            $blocks[] = array(0, $line);
            $blocks[] = array(2);
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
