<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Model;

use Amasty\SecurityAuth\Api\AuthRepositoryInterface;
use Amasty\SecurityAuth\Api\Data\AuthInterface;
use Amasty\SecurityAuth\Model\ResourceModel\Auth as AuthResource;
use Amasty\SecurityAuth\Model\AuthFactory;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @var ResourceModel\Auth\
     */
    protected $authResource;

    /**
     * @var AuthFactory
     */
    protected $authFactory;

    /**
     * AuthRepository constructor.
     * @param AuthResource $authResource
     * @param \Amasty\SecurityAuth\Model\AuthFactory $columnFactory
     */
    public function __construct(
        AuthResource $authResource,
        AuthFactory $columnFactory
    ) {
        $this->authResource = $authResource;
        $this->authFactory = $columnFactory;
    }

    /**
     * @param int $id Column ID.
     *
     * @return AuthInterface
     */
    public function get($id)
    {
        $model = $this->authFactory->create();
        $this->authResource->load($model, $id);

        return $model;
    }

    /**
     * @param int $id Column ID.
     *
     * @return AuthInterface
     */
    public function getByUserId($id)
    {
        $model = $this->authFactory->create();
        $this->authResource->load($model, $id, AuthInterface::USER_ID);

        return $model;
    }

    /**
     * @param AuthInterface $entity
     * @return $this
     */
    public function delete(AuthInterface $entity)
    {
        return $this->authResource->delete($entity);
    }

    /**
     * @param AuthInterface $entity
     * @return $this
     */
    public function save(AuthInterface $entity)
    {
        return $this->authResource->save($entity);
    }
}