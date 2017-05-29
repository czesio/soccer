<?php
namespace App\Repository;

use App\Repository\RepositoryInterface;
use Doctrine\DBAL\Connection;
use App\Entity\User;

class UserRepository implements RepositoryInterface
{
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param User $user
     */
    public function save($user)
    {
        $userData = array(
            'nickname' => $user->getNickname()
        );

        $this->db->insert('user', $userData);
        $id = $this->db->lastInsertId();
        $user->setId($id);
    }
}