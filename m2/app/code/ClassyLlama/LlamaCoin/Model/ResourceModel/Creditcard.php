<?php

namespace ClassyLlama\LlamaCoin\Model\ResourceModel;

class Creditcard extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    protected function _construct() {
        $this->_init('optimal_creditcard', 'entity_id');
    }
    
    public function loadByProfileId(\ClassyLlama\LlamaCoin\Model\Creditcard $object, $profileId)
    {
        $adapter    = $this->_getConnection('write');
        $where      = $adapter->quoteInto("profile_id = ?", $profileId);

        $select     = $adapter->select()
            ->from($this->getMainTable())
            ->where($where);
        if($data = $adapter->fetchRow($select))
        {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;

    }


    public function loadByProfileAndToken(\ClassyLlama\LlamaCoin\Model\Creditcard $object, $profileId, $paymentToken)
    {
        $adapter        = $this->_getConnection('write');
        $whereProfile   = $adapter->quoteInto("profile_id = ?", $profileId);
        $whereToken     = $adapter->quoteInto("payment_token = ?", $paymentToken);

        $select     = $adapter->select()
            ->from($this->getMainTable())
            ->where($whereProfile)
            ->where($whereToken);
        if($data = $adapter->fetchRow($select))
        {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;

    }

    public function loadByMerchantCustomerId(\ClassyLlama\LlamaCoin\Model\Creditcard $object, $merchantCustomerId)
    {
        $adapter    = $this->_getConnection('write');
        $where      = $adapter->quoteInto("merchant_customer_id = ?", $merchantCustomerId);

        $select     = $adapter->select()
            ->from($this->getMainTable())
            ->where($where);
        if($data = $adapter->fetchRow($select))
        {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;

    }
}