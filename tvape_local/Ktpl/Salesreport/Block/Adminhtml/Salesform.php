<?php

class Ktpl_Salesreport_Block_Adminhtml_Salesform extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Retrieves Setup Instance
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    
    public function Setup()
    {
        if(!empty($_POST)){
            return $_POST;
        }
        return 0;
    }

}