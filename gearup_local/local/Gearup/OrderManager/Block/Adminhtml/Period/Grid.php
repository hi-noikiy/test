<?php

class Gearup_OrderManager_Block_Adminhtml_Period_Grid extends Hatimeria_OrderManager_Block_Adminhtml_Period_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('period_grid');
        $this->setDefaultSort('date_from');
        $this->setDefaultDir('DESC');
        $this->setDefaultLimit(50);
        $defaultFilters = array();
        $sessionParamName = $this->getId() . $this->getVarNameFilter();
        Mage::getSingleton('adminhtml/session')->setData($sessionParamName, '');
        $this->setDefaultFilter($defaultFilters);
   }

}