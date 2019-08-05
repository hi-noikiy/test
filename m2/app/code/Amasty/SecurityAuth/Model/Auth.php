<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Model;

use Amasty\SecurityAuth\Api\Data\AuthInterface;
use Magento\Framework\Model\Context;

class Auth extends \Magento\Framework\Model\AbstractModel implements AuthInterface
{

    protected function _construct()
    {
        $this->_init('Amasty\SecurityAuth\Model\ResourceModel\Auth');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getData(AuthInterface::ID);
    }

    /**
     * @param string $id
     * @return AuthInterface
     */
    public function setId($id)
    {
        return $this->setData(AuthInterface::ID, $id);
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->getData(AuthInterface::USER_ID);
    }

    /**
     * @param string $id
     * @return AuthInterface
     */
    public function setUserId($id)
    {
        return $this->setData(AuthInterface::USER_ID, $id);
    }

    /**
     * @return string
     */
    public function getEnable()
    {
        return $this->getData(AuthInterface::ENABLE);
    }

    /**
     * @param string $enable
     * @return AuthInterface
     */
    public function setEnable($enable)
    {
        return $this->setData(AuthInterface::ENABLE, $enable);
    }

    /**
     * @return int
     */
    public function getTwoFactorToken()
    {
        return $this->getData(AuthInterface::TWO_FACTOR_TOKEN);
    }

    /**
     * @param int $token
     * @return AuthInterface
     */
    public function setTwoFactorToken($token)
    {
        return $this->setData(AuthInterface::TWO_FACTOR_TOKEN, $token);
    }
}
