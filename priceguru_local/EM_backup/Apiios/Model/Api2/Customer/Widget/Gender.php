<?php
class EM_Apiios_Model_Api2_Customer_Widget_Gender extends Mage_Customer_Block_Widget_Abstract
{
    /**
     * Initialize block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('customer/widget/gender.phtml');
    }

    /**
     * Check if gender attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->_getAttribute('gender')->getIsVisible();
    }

    /**
     * Check if gender attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return (bool)$this->_getAttribute('gender')->getIsRequired();
    }

    public function getOptionGender(){
        return Mage::getResourceSingleton('customer/customer')->getAttribute('gender')->getSource()->getAllOptions();
    }

    public function buildFieldList(){
        $result = array();
        /* Prefix Field */
        if($this->isEnabled()){
            $field = array();
            $field['label'] = $this->helper('customer')->__('Gender');
            $field['required'] = $this->isRequired();
            $field['name'] = $this->getFieldName('gender');
            $field['type'] = 'select';
            $field['options'] = $this->getOptionGender();
            $result[] = $field;
        }
        return $result;
    }
}
