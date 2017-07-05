<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

/**
 * Description of Participant
 *
 * @author dinel
 */
class Participant {
    protected $id;
    
    protected $name;
    
    protected $email;
    
    protected $age;
    
    protected $text;
    
    public function getText() {
        return $this->text;
    }
    
    public function setText($text) {
        $this->text = $text;
    }
}
