<?php
namespace App\Entity;

class Game
{
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     * When the like entity was created.
     *
     * @var DateTime
     */
    protected $createdAt;

    /**
     * Season
     *
     * @var \App\Entity\Season
     */
    protected $season;

    protected $season_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getSeason()
    {
        return $this->season;
    }

    public function setSeason($season)
    {
        $this->season = $season;
    }

    public function getSeasonId()
    {
        return $this->season_id;
    }

    public function setSeasonId($season_id)
    {
        $this->season_id = $season_id;
    }
}