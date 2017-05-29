<?php
namespace App\Entity;

class Season
{
    private $id;

    private $created_at;


    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->created_at = $createdAt;
    }

}