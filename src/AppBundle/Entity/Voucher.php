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
 * Description of Voucher
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="voucher")
 */
class Voucher {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $text;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $code;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $available;
    
    public function getId() {
        return $this->id;
    }

    public function getText() {
        return $this->text;
    }
    
    public function getCode() {
        return $this->code;
    }

    public function getAvailable() {
        return $this->available;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setText($text) {
        $this->text = $text;
        return $this;
    }
    
    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    public function setAvailable($available) {
        $this->available = $available;
        return $this;
    }     
}
