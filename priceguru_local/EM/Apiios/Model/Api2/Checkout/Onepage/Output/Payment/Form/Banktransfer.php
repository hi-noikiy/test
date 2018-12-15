<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Payment_Form_Banktransfer extends Mage_Core_Model_Abstract
{
    /**
     * Get fields list for ccsave payment
     */
    public function toArrayFields(){
        $fields = array();
        $field = array();
        $field['type'] = 'label';
		$field['value'] = $this->getMethod()->getInstructions();
        $fields[] = $field;

        return $fields;
    }
}
?>