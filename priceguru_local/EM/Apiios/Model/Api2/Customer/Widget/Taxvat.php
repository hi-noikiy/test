<?php
class EM_Apiios_Model_Api2_Customer_Widget_Taxvat extends EM_Apiios_Model_Api2_Customer_Widget_Abstract
{
    public function isEnabled()
    {
        return (bool)$this->_getAttribute('taxvat')->getIsVisible();
    }

    public function isRequired()
    {
        return (bool)$this->_getAttribute('taxvat')->getIsRequired();
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Retrieve customer attribute instance
     *
     * @param string $attributeCode
     * @return Mage_Customer_Model_Attribute|false
     */
    protected function _getAttribute($attributeCode)
    {
        return Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);
    }

    public function buildFieldList(){
        $result = array();
        /* Prefix Field */
        if($this->isEnabled()){
            $field = array();
            $field['label'] = $this->helper('customer')->__('Tax/VAT number');
            $field['required'] = $this->isRequired();
            $field['name'] = $this->getFieldName('taxvat');
            $field['type'] = 'text';
            $taxVat = $this->getData('tax_vat');
            if(!is_null($taxVat))
                $field['value'] = $taxVat;
            $result[] = $field;
        }
        return $result;
    }
}
