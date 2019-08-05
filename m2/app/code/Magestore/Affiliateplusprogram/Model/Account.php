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
class Account extends AbstractModel
{

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplusprogram\Model\ResourceModel\Account');
    }

    /**
     * Save all accounts to a product
     * @return $this
     */
    public function saveAll(){
        if ($this->getProgramId()){
            $now1 = new \DateTime();
            $this->setJoined($now1);
            $newAccountIds = array();
            if ($this->getAccountIds() && is_array($this->getAccountIds())){
                $newAccountIds = array_combine($this->getAccountIds(),$this->getAccountIds());
            }
            $collection = $this->getCollection()
                ->addFieldToFilter('program_id',$this->getProgramId());
            foreach ($collection as $account){
                $accountId = $account->getAccountId();
                if (in_array($accountId, $newAccountIds)){
                    unset($newAccountIds[$accountId]);
                }else{
                    $this->setId($account->getId())->delete();
                }
            }
            $this->addAccount($newAccountIds);
        }
        return $this;
    }

    /**
     * @param $accountIds
     * @return $this
     */
    public function addAccount($accountIds){
        foreach ($accountIds as $account){
            if (is_numeric($account)){
                $this->setAccountId($account)->setId(null)->save();
            }
        }
        return $this;
    }

    /**
     * @param $accountIds
     * @return $this
     */
    public function removeAccount($accountIds){
        foreach ($accountIds as $account){
            if (is_numeric($account)){
                $this->setId($account)->delete();
            }
        }
        return $this;
    }
}
