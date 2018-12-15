<?php 
Class EM_Customercomment2_Model_Mysql4_Customercomment2_Quote_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
	public function _construct()
    {
        parent::_construct();
        $this->_init('customercomment2/customercomment2_quote');
    }
}