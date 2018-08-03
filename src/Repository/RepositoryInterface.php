<?php declare(strict_types=1);

namespace Football\Repository;

interface RepositoryInterface
{
    /**
     * Load data
     * @param  mixed $args
     */
    public function load($args): void;

    /**
     * Search data
     * @param  mixed $args
     * @return mixed
     */
    public function search($args);

    /**
     * List all data in repository
     * @param  mixed $args
     * @return mixed
     */
    public function list($args);
}
