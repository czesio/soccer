<?php

namespace App\Entity;

class User
{
    private $id;
    private $nickname;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function setNickname($value)
    {
        $this->nickname = $value;
    }
}