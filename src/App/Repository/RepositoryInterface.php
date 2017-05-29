<?php
namespace App\Repository;

interface RepositoryInterface
{
    /**
     * Saves the entity to the database.
     *
     * @param object $entity
     */
    public function save($entity);
}