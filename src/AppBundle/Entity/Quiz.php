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
 * Description of Quiz
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="quiz")
 */
class Quiz {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=256)
     */
    protected $description;
    
    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="quiz")
     */
    protected $questions;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $instructions;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;
    
    public function __construct() {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
    }   
    
    public function getId() {
        return $this->id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getQuestions() {
        return $this->questions;
    }

    public function getInstructions() {
        return $this->instructions;
    }

    public function getText() {
        return $this->text;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setQuestions($questions) {
        $this->questions = $questions;
        return $this;
    }
    
    public function addQuestion($question) {
        $this->questions[] = $question;
        $question->setQuiz($this);
    }

    public function setInstructions($instructions) {
        $this->instructions = $instructions;
        return $this;
    }

    public function setText($text) {
        $this->text = $text;
        return $this;
    }
}
