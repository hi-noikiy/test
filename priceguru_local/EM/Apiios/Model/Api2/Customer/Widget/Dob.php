<?php
class EM_Apiios_Model_Api2_Customer_Widget_Dob extends EM_Apiios_Model_Api2_Customer_Widget_Abstract
{
    public function isEnabled()
    {
        return (bool)$this->_getAttribute('dob')->getIsVisible();
    }

    public function isRequired()
    {
        return (bool)$this->_getAttribute('dob')->getIsRequired();
    }
    
    /**
     * Returns format which will be applied for DOB in javascript
     *
     * @return string
     */
    public function getDateFormat()
    {
        return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    public function buildFieldList(){
        $result = array();
        if($this->isEnabled()){
            $helper = $this->helper('customer');
            $result['label'] = $helper->__('Date of Birth');
            $result['type'] = 'date';
            $result['name'] = $this->getFieldName('dob');
            $result['required'] = $this->isRequired();

            $children = array();

            $field = array();
            $field['label'] = $helper->__('DD');
            $field['name'] = $this->getFieldName('day');
            $field['type'] = 'text';
            $children[] = $field;

            $field = array();
            $field['label'] = $helper->__('MM');
            $field['name'] = $this->getFieldName('month');
            $field['type'] = 'text';
            $children[] = $field;

            $field = array();
            $field['label'] = $helper->__('YYYY');
            $field['name'] = $this->getFieldName('year');
            $field['type'] = 'text';
            $children[] = $field;

            $result['children'] = $children;
            $result['format'] = $this->getDateFormat();
        }
        return $result;
    }
}
?>