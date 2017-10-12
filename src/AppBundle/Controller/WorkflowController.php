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
                ));
                
            case 'thank_you':
                $participant = $this->getDoctrine()
                                    ->getRepository('AppBundle:Participant')
                                    ->find($request->getSession()->get('participantID'));
                $participant->setFinished(1);
                $em = $this->getDoctrine()->getManager();
                $em->persist($participant);
                $em->flush();                
                
                return $this->render('default/thankyou.html.twig', array(
                    'participant' => $request->getSession()->get('participantID'),
                ));
                
            default:
                if(preg_match("/^prereading_([0-9]+)_([0-9]+)$/", $state, $matches)) {                    
                    $quiz = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[1]);
                    
                    $mcq = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[2]);
                    
                    return $this->render('default/display_quiz.html.twig', array(
                            'quiz' => $quiz,
                            'mcq' => $mcq,
                    ));
                }
                
                if(preg_match("/^mcq_([0-9]+)$/", $state, $matches)) {                                        
                    $mcq = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[1]);
                    
                    return $this->render('default/display_quiz.html.twig', array(
                            'quiz' => NULL,
                            'mcq' => $mcq,
                    ));
                }
                
                if(preg_match("/^subjective_([0-9]+)$/", $state, $matches)) {                                        
                    $subjective_survey = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[1]);
                    
                    return $this->render('default/post_test_quiz.html.twig', array(
                            'quiz' => $subjective_survey,
                    ));
                }
                
                if(preg_match("/^reviews_([0-9]+)$/", $state, $matches)) {                                        
                    $reviews_survey = $this->getDoctrine()
                                 ->getRepository('AppBundle:Quiz')
                                 ->find($matches[1]);
                    
                    return $this->render('default/post_test_quiz.html.twig', array(
                            'quiz' => $reviews_survey,
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
                //$sequences[1], 
                ['subjective_9', 'reviews_10', 'thank_you']);        
        
        $session->set('workflow', $workflow);
        
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
        $em = $this->getDoctrine()->getManager();
        $em->persist($participant);
        $em->flush();
        
        $id = $participant->getId();
        $request->getSession()->set("participantID", $id);
        
        $message = (new \Swift_Message('Hello Email'))
        ->setFrom('c.orasan@wlv.ac.uk')
        ->setTo('c.orasan@gmail.com')
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
        $voucher = $request->request->get("voucher");
        $participantID = $request->getSession()->get("participantID");
        $participant = $this->getDoctrine()
                            ->getRepository('AppBundle:Participant')
                            ->find($participantID);
        $participant->setVoucher($voucher);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($participant);
        $em->flush();
        
        return new JsonResponse();
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
}