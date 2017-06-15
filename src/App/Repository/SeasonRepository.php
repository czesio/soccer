<?php
namespace App\Repository;

use App\Repository\RepositoryInterface;
use Doctrine\DBAL\Connection;
use App\Entity\Season;

class SeasonRepository implements RepositoryInterface
{
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function save($season)
    {
        $seasonData = array(
            'created_at' => $season->getCreatedAt()->format('Y-m-d H:i:s'),
        );

        $this->db->insert('season', $seasonData);
        $id = $this->db->lastInsertId();
        $season->setId($id);
    }

    /**
     * Returns an artist matching the supplied id.
     *
     * @param integer $id
     *
     * @return \App\Entity\Season|false An entity object if found, false otherwise.
     */
    public function find($id)
    {
        $seasonData = $this->db->fetchAssoc('SELECT * FROM season WHERE id = ?', array($id));
        return $seasonData ? $this->buildSeason($seasonData) : FALSE;
    }


    public function findLastSeason()
    {
        $seasonData = $this->db->fetchAssoc('SELECT * FROM season ORDER BY id DESC LIMIT 1');
        return $seasonData ? $this->buildSeason($seasonData) : FALSE;
    }
    /**
     * Instantiates an season entity and sets its properties using db data.
     *
     * @param array $seasonData
     *   The array of db data.
     *
     * @return \App\Entity\Season
     */
    protected function buildSeason($seasonData)
    {
        $season = new Season();
        $createdAt = new \DateTime($seasonData['created_at']);
        $season->setCreatedAt($createdAt);
        $season->setId($seasonData['id']);
        return $season;
    }
}