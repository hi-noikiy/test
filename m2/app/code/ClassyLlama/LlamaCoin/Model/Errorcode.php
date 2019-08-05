<?php

namespace ClassyLlama\LlamaCoin\Model;

class Errorcode extends \Magento\Framework\Model\AbstractModel
{
    
    protected function _construct() {
        $this->_init(\ClassyLlama\LlamaCoin\Model\ResourceModel\Errorcode::class);
    }
    
    public function loadByCode($code)
    {
        $this->_getResource()->loadByCode($this, $code);
        return $this;
    }
    
}