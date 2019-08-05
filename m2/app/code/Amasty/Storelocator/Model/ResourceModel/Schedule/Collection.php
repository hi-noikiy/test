<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel\Schedule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Storelocator\Model\Schedule', 'Amasty\Storelocator\Model\ResourceModel\Schedule');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
