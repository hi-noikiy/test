<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Api;

use Amasty\SecurityAuth\Api\Data\AuthInterface;

interface AuthRepositoryInterface
{
    /**
     * @param int $id Flag ID.
     * @return AuthInterface
     */
    public function get($id);

    /**
     * @param AuthInterface $entity
     * @return mixed
     */
    public function delete(AuthInterface $entity);

    /**
     * @param AuthInterface $entity
     * @return mixed
     */
    public function save(AuthInterface $entity);
}
