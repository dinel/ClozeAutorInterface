<?php

/*
 * Copyright 2018 dinel.
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
 * Description of Resume
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="resume")
 */
class Resume {
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
     * @ORM\Column(type="text")
     */
    protected $sequence;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $instructions;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $used;
    
    public function getId() {
        return $this->id;
    }

    public function getParticipant() {
        return $this->participant;
    }

    public function getSequence() {
        return $this->sequence;
    }
    
    public function getInstructions() {
        return $this->instructions;
    }
        
    public function getUsed() {
        return $this->used;
    }

    public function setUsed($used) {
        $this->used = $used;
        return $this;
    }
}
