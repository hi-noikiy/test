<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Api\Data;

interface AuthInterface
{
    const ID = 'id';

    const USER_ID = 'user_id';

    const ENABLE = 'enable';

    const TWO_FACTOR_TOKEN = 'two_factor_token';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \Amasty\SecurityAuth\Api\Data\AuthInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getUserId();

    /**
     * @param int $id
     * @return \Amasty\SecurityAuth\Api\Data\AuthInterface
     */
    public function setUserId($id);
    
    /**
     * @return string
     */
    public function getEnable();

    /**
     * @param string $enable
     * @return \Amasty\SecurityAuth\Api\Data\AuthInterface
     */
    public function setEnable($enable);

    /**
     * @return int
     */
    public function getTwoFactorToken();

    /**
     * @param int $token
     * @return \Amasty\SecurityAuth\Api\Data\AuthInterface
     */
    public function setTwoFactorToken($token);
}
