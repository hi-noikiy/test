<?php

class Gearup_Cnetdescription_Block_Adminhtml_Cnetdescription_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
         
        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('cnetdescription_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
}