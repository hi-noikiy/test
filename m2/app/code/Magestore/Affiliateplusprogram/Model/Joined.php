<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Model;

/**
 * Class Program
 * @package Magestore\Affiliateplusprogram\Model
 */
class Joined extends AbstractModel
{
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplusprogram\Model\ResourceModel\Joined');
    }

    /**
     *
     * @param null $program
     * @param null $account
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateJoined($program = null, $account = null) {
        if (is_object($program)) {
            $program = $program->getId();
        }
        if (is_object($account)) {
            $account = $account->getId();
        }
        $this->_getResource()->updateJoinedDatabase($program, $account);
        return $this;
    }

    /**
     * @param null $program
     * @param null $account
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertJoined($program = null, $account = null) {
        if (is_object($program)) {
            $program = $program->getId();
        }
        if (is_object($account)) {
            $account = $account->getId();
        }
        if ($program) {
            $this->setData('program_id',  $program);
        }
        if ($account) {
            $this->setData('account_id', $account);
        }
        $this->_getResource()->insertJoinedDatabase($this);
        return $this;
    }
}
