<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Payment_Form_Onepay extends Mage_Core_Model_Abstract
{
	/**
     * Get fields list for ccsave payment
     */
    public function toArrayFields(){
        $fields = array();
        $helper = Mage::helper('payment');

        /* Name Card Field */
        $field = array();
        $field['label'] = $helper->__('One Pay ATM Cart');
        $field['required'] = true;
        $field['name'] = 'onepay';
        $field['type'] = 'text';
        $fields[] = $field;

        /* Credit Card Type field */
        $field = array();
        $field['label'] = $helper->__('One Pay Credit Cart');
        $field['required'] = true;
        $field['name'] = 'onepayquocte';
        $field['type'] = 'text';
        

        return $fields;
    }
}