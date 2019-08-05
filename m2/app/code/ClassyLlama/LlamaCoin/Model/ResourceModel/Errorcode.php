<?php

namespace ClassyLlama\LlamaCoin\Model\ResourceModel;

class Errorcode extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    protected function _construct() {
        $this->_init('optimal_errorcode', 'entity_id');
    }
    
    public function loadByCode(\ClassyLlama\LlamaCoin\Model\Errorcode $object, $code)
    {
        $adapter    = $this->_getConnection('write');
        $where      = $adapter->quoteInto("code = ?", $code);
        $select     = $adapter->select()
                        ->from($this->getMainTable())
                        ->where($where);

        if ($data = $adapter->fetchRow($select)) {
            $object->setData($data);
            $this->_afterLoad($object);
        }

        return $this;

    }
  
}