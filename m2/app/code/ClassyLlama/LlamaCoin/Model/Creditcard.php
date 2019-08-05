<?php

namespace ClassyLlama\LlamaCoin\Model;

class Creditcard extends \Magento\Framework\Model\AbstractModel
{
    
    protected function _construct() {
        $this->_init(\ClassyLlama\LlamaCoin\Model\ResourceModel\Creditcard::class);
    }
    
    public function loadByProfileId($profileId)
    {
        $this->_getResource()->loadByProfileId($this, $profileId);
        return $this;
    }

    public function loadByProfileAndToken($profileId, $paymentToken)
    {
        $this->_getResource()->loadByProfileAndToken($this, $profileId, $paymentToken);
        return $this;

    }

    public function loadByMerchantCustomerId($merchantCustomerId)
    {
        $this->_getResource()->loadByMerchantCustomerId($this, $merchantCustomerId);
        return $this;

    }

    public function loadByCustomerId($customerId)
    {
        return $this->load($customerId, 'customer_id');
    }
}