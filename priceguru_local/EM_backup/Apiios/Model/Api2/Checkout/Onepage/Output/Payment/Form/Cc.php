<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Payment_Form_Cc extends Mage_Core_Model_Abstract
{
    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  Mage::helper('payment')->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0=>Mage::helper('payment')->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrive has verification configuration
     *
     * @return boolean
     */
    public function hasVerification()
    {
        if ($this->getMethod()) {
            $configData = $this->getMethod()->getConfigData('useccv');
            if(is_null($configData)){
                return true;
            }
            return (bool) $configData;
        }
        return true;
    }

    /*
    * Whether switch/solo card type available
    */
    public function hasSsCardType()
    {
        $availableTypes = explode(',', $this->getMethod()->getConfigData('cctypes'));
        $ssPresenations = array_intersect(array('SS', 'SM', 'SO'), $availableTypes);
        if ($availableTypes && count($ssPresenations) > 0) {
            return true;
        }
        return false;
    }

    /*
    * solo/switch card start year
    * @return array
    */
    public function getSsStartYears()
    {
        $years = array();
        $first = date("Y");

        for ($index=5; $index>=0; $index--) {
            $year = $first - $index;
            $years[$year] = $year;
        }
        $years = array(0=>Mage::helper('payment')->__('Year'))+$years;
        return $years;
    }

    /**
     * Get fields list for ccsave payment
     */
    public function toArrayFields(){
        $fields = array();
        $helper = Mage::helper('payment');

        /* Name Card Field */
        $field = array();
        $field['label'] = $helper->__('Name on Card');
        $field['required'] = true;
        $field['name'] = 'cc_owner';
        $field['type'] = 'text';
        $fields[] = $field;

        /* Credit Card Type field */
        $field = array();
        $field['label'] = $helper->__('Credit Card Type');
        $field['required'] = true;
        $field['name'] = 'cc_type';
        $field['type'] = 'select';
        $options = $this->getCcAvailableTypes();
        $toArray = array(array(
            'value' =>  '',
            'label' =>  $helper->__('--Please Select--')
        ));
        foreach($options as $_typeCode => $_typeName){
            $toArray[] = array(
                'value' =>  $_typeCode,
                'label' =>  $_typeName
            );
        }
        $field['options'] = $toArray;
        $fields[] = $field;

        /* Credit Card Number Field */
        $field = array();
        $field['label'] = $helper->__('Credit Card Number');
        $field['required'] = true;
        $field['name'] = 'cc_number';
        $field['type'] = 'text';
        $fields[] = $field;

        /* Expiration Date Field */
        $field = array();
        $field['label'] = $helper->__('Expiration Date');
        $field['required'] = true;
        $field['type'] = 'group';

        $childs = array();
        $ccMonths = array();
        foreach($this->getCcMonths() as $k=>$v){
            $ccMonths[] = array(
                'value' =>  $k?$k:'',
                'label' =>  $v
            );
        }

        $ccYear = array();
        foreach($this->getCcYears() as $k=>$v){
            $ccYear[] = array(
                'value' =>  $k?$k:'',
                'label' =>  $v
            );
        }

        $fieldChild = array();
        $fieldChild['required'] = true;
        $fieldChild['name'] = 'cc_exp_month';
        $fieldChild['type'] = 'select';
        $fieldChild['options'] = $ccMonths;
        $childs[] = $fieldChild;

        $fieldChild = array();
        $fieldChild['required'] = true;
        $fieldChild['name'] = 'cc_exp_year';
        $fieldChild['type'] = 'select';
        $fieldChild['options'] = $ccYear;
        $childs[] = $fieldChild;

        $field['children'] = $childs;
        $fields[] = $field;

        if($this->hasVerification()){
            /* Credit Card Number Field */
            $field = array();
            $field['label'] = $helper->__('Card Verification Number');
            $field['required'] = true;
            $field['name'] = 'cc_cid';
            $field['type'] = 'text';
            $fields[] = $field;
        }

        if ($this->hasSsCardType()){
            $field = array();
            $field['label'] = $helper->__('Switch/Solo/Maestro Only');
            $field['required'] = true;
            $field['name'] = '';
            $field['type'] = 'label';
            $fields[] = $field;

            /* Start Date Field */
            $field = array();
            $field['label'] = $helper->__('Start Date');
            $field['required'] = true;
            $field['type'] = 'group';

            $childs = array();
            $fieldChild = array();
            $fieldChild['required'] = true;
            $fieldChild['name'] = 'cc_ss_start_month';
            $fieldChild['type'] = 'select';
            $fieldChild['options'] = $ccMonths;
            $childs[] = $fieldChild;

            $fieldChild = array();
            $fieldChild['required'] = true;
            $fieldChild['name'] = 'cc_ss_start_year';
            $fieldChild['type'] = 'select';
            $fieldChild['options'] = $ccYear;
            $childs[] = $fieldChild;

            $field['children'] = $childs;
            $fields[] = $field;
        }

        return $fields;
    }
}
?>