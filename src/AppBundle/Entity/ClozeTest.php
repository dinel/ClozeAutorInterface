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
 * Description of ClozeTest
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="clozetest")
 */
class ClozeTest {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=1024)
     */
    protected $title;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $original_text;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $text;
    
    public function getId() {
        return $this->id;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getOriginalText() {
        return $this->original_text;
    }
        
    public function getText() {
        return $this->text;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    public function setOriginalText($original_text) {
        $this->original_text = $original_text;
        return $this;
    }
        
    public function setText($text) {
        $this->text = $text;
        return $this;
    }       
}
