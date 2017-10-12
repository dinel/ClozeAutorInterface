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
 * Description of Log
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="log")
 */
class Log {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $participant;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $timeMiliseconds;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $timeClear;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $message;
    
    public function getId() {
        return $this->id;
    }

    public function getParticipant() {
        return $this->participant;
    }

    public function getTimeMiliseconds() {
        return $this->timeMiliseconds;
    }

    public function getTimeClear() {
        return $this->timeClear;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setParticipant($participant) {
        $this->participant = $participant;
        return $this;
    }

    public function setTimeMiliseconds($timeMiliseconds) {
        $this->timeMiliseconds = $timeMiliseconds;
        return $this;
    }

    public function setTimeClear($timeClear) {
        $this->timeClear = $timeClear;
        return $this;
    }

    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }
}
