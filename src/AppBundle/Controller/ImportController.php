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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


/**
 * Description of ImportController
 *
 * @author dinel
 */
class ImportController extends Controller 
{
    /**
     * @Route("/admin/import/{name}")
     */
    public function importQuizAction($name) {
        $handle = fopen("/tmp/" . $name, "r");
        if ($handle) {
            $em = $this->getDoctrine()->getManager();
            $quiz = new \AppBundle\Entity\Quiz();
            $quiz->setInstructions("soon to come");
            $question = NULL;
            
            $quiz->setDescription(fgets($handle) . " " . fgets($handle));
            while (($line = fgets($handle)) !== false) {
                if(preg_match("/^[0-9]+\.(.*)$/", $line, $matches)) {
                    if($question) {
                        $em->persist($question);
                    }
                    $question = new \AppBundle\Entity\Question();
                    $question->setText($matches[1]);
                    $quiz->addQuestion($question);
                }
                
                if(preg_match("/^\+?[A-Za-z]\)(.*)$/", $line, $matches)) {
                    $answer = new \AppBundle\Entity\Answer();
                    $answer->setText($matches[1]);
                    if($line[0] == "+") {
                        $answer->setCorrect(1);
                    }
                    $question->addAnswer($answer);
                    $em->persist($answer);
                }
            }
            $em->persist($question);
            $em->persist($quiz);
            $em->flush();

            fclose($handle);
        } else {
            return new Response("fail");
        } 
                        
        return new Response("success");
    }
}
