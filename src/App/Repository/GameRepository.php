<?php
namespace App\Repository;

use App\Repository\RepositoryInterface;
use Doctrine\DBAL\Connection;
use App\Entity\Game;

class GameRepository implements RepositoryInterface
{
    protected $db;

    protected $seasonRepository;

    public function __construct(Connection $db, $seasonRepository)
    {
        $this->db = $db;
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param Game $game
     */
    public function save($game)
    {
        $gameData = array(
            'season_id' => $game->getSeasonId(),
            'created_at' => $game->getCreatedAt()->format('Y-m-d H:i:s'),
        );

        $this->db->insert('game', $gameData);
        $id = $this->db->lastInsertId();
        $game->setId($id);
    }

    public function findGamesForSeason($seasonId = null, $orderBy = array())
    {
        if (null == $seasonId) {
            $season = $this->seasonRepository->findLastSeason();
            if ($season)
                $seasonId = $season->getId();
        }
        $conditions = array(
            'season_id' => $seasonId,
        );
        $games = $this->getGames($conditions, $orderBy);
        if ($games) {
            return $games;
        }
    }

    protected function getGames($conditions, $orderBy = array())
    {
        // Provide a default orderBy.
        if (!$orderBy) {
            $orderBy = array('game_id' => 'ASC');
        }

        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('g.id AS game_id, u.id AS user_id, u.nickname, ug.score, g.season_id ')
            ->from('game', 'g')
            ->join('g', 'user_game', 'ug', 'g.id = ug.game_id')
            ->join('ug', 'user', 'u', 'ug.user_id = u.id')
            ->orderBy('ug.' . key($orderBy), current($orderBy));
        $parameters = array();
        foreach ($conditions as $key => $value) {
            $parameters[':' . $key] = $value;
            $where = $queryBuilder->expr()->eq('g.' . $key, ':' . $key);
            $queryBuilder->andWhere($where);
        }
        $queryBuilder->setParameters($parameters);
        $statement = $queryBuilder->execute();
        $gamesData = $statement->fetchAll();

        return $gamesData;
    }
}