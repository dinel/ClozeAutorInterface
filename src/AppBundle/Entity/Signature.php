<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Signature
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="signature")
 */
class Signature {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * field which indicates the type if information 
     * @ORM\Column(type="string", length=256)
     */
    protected $email;
    
    /**
     * field which indicates the type if information 
     * @ORM\Column(type="string", length=4096)
     */
    protected $key;
    
    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getKey() {
        return $this->key;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function setKey($key) {
        $this->key = $key;
        return $this;
    }

    
}
