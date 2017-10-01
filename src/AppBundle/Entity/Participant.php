<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Participant
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="participant")
 */
class Participant {
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    protected $name;
    
    protected $email;
    
    protected $age;
    
    protected $years_edu;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
        
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    
    public function getAge() {
        return $this->age;
    }
    
    public function setAge($age) {
        $this->age = $age;
    }    
    
    public function getText() {
        return $this->text;
    }
    
    public function setText($text) {
        $this->text = $text;
    }            
    
    public function getYearsEdu() {
        $this->years_edu;
    }
    
    public function setYearsEdu($years_edu) {
        $this->years_edu = $years_edu;
    }
}
