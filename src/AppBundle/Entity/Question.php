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

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Question
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="question")
 */
class Question {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=1024)
     */
    protected $text;
    
    /**
     * @ORM\ManyToOne(targetEntity="Quiz", inversedBy="questions")
     * @ORM\JoinColumn(name="quiz_id", referencedColumnName="id")
     */
    protected $quiz;
    
    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="question")
     */
    protected $answers;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $open;
    
    public function __construct() {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    } 
    
    public function getId() {
        return $this->id;
    }

    public function getText() {
        return $this->text;
    }

    public function getQuiz() {
        return $this->quiz;
    }

    public function getAnswers() {
        return $this->answers;
    }
    
    public function getOpen() {
        return $this->open;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setText($text) {
        $this->text = $text;
        return $this;
    }

    public function setQuiz($quiz) {
        $this->quiz = $quiz;
        return $this;
    }

    public function setAnswers($answers) {
        $this->answers = $answers;
        return $this;
    }
    
    public function addAnswer($answer) {
        $this->answers[] = $answer;
        $answer->setQuestion($this);
        
        return $this;
    }
    
    public function getNoCorrectAnswers() {
        $count = 0;
        foreach($this->answers as $answer) {
            if($answer->getCorrect()) {
                $count++;
            }
        }
        
        return $count ? $count : 1;
    }
    
    public function setOpen($open)  {
        $this->open = $open;
    }
}
