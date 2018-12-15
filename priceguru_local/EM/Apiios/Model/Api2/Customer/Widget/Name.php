<?php
class EM_Apiios_Model_Api2_Customer_Widget_Name extends EM_Apiios_Model_Api2_Customer_Widget_Abstract
{
    /**
     * Can show config value
     *
     * @param string $key
     * @return bool
     */
    protected function _showConfig($key)
    {
        return (bool)$this->getConfig($key);
    }

    /**
     * Can show prefix
     *
     * @return bool
     */
    public function showPrefix()
    {
        return (bool)$this->_getAttribute('prefix')->getIsVisible();
    }

    /**
     * Define if prefix attribute is required
     *
     * @return bool
     */
    public function isPrefixRequired()
    {
        return (bool)$this->_getAttribute('prefix')->getIsRequired();
    }

    /**
     * Retrieve name prefix drop-down options
     *
     * @return array|bool
     */
    public function getPrefixOptions()
    {
        return $this->_prepareNamePrefixSuffixOptions(
            Mage::helper('customer/address')->getConfig('prefix_options', $this->getStore())
        );
    }

    /**
     * Define if middle name attribute can be shown
     *
     * @return bool
     */
    public function showMiddlename()
    {
        return (bool)$this->_getAttribute('middlename')->getIsVisible();
    }

    /**
     * Define if middlename attribute is required
     *
     * @return bool
     */
    public function isMiddlenameRequired()
    {
        return (bool)$this->_getAttribute('middlename')->getIsRequired();
    }

    /**
     * Define if suffix attribute can be shown
     *
     * @return bool
     */
    public function showSuffix()
    {
        return (bool)$this->_getAttribute('suffix')->getIsVisible();
    }

    /**
     * Define if suffix attribute is required
     *
     * @return bool
     */
    public function isSuffixRequired()
    {
        return (bool)$this->_getAttribute('suffix')->getIsRequired();
    }

    /**
     * Retrieve name suffix drop-down options
     *
     * @return array|bool
     */
    public function getSuffixOptions()
    {
        return $this->_prepareNamePrefixSuffixOptions(
            Mage::helper('customer/address')->getConfig('suffix_options', $this->getStore())
        );
    }

    /**
     * Retrieve customer or customer address attribute instance
     *
     * @param string $attributeCode
     * @return Mage_Customer_Model_Attribute|false
     */
    protected function _getAttribute($attributeCode)
    {
        if ($this->getForceUseCustomerAttributes() || $this->getObject() instanceof Mage_Customer_Model_Customer) {
            return Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);;
        }

        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', $attributeCode);

        if ($this->getForceUseCustomerRequiredAttributes() && $attribute && !$attribute->getIsRequired()) {
            $customerAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);
            if ($customerAttribute && $customerAttribute->getIsRequired()) {
                $attribute = $customerAttribute;
            }
        }

        return $attribute;
    }

    /**
     * Unserialize and clear name prefix or suffix options
     *
     * @param string $options
     * @return array|bool
     */
    protected function _prepareNamePrefixSuffixOptions($options)
    {
        $options = trim($options);
        if (empty($options)) {
            return false;
        }
        $result = array();
        $options = explode(';', $options);
        foreach ($options as $value) {
            $value = $this->helper('customer')->escapeHtml(trim($value));
            $result[] = array(
                'value' =>  $value,
                'label' =>  $value
            );
        }
        return $result;
    }

    public function buildFieldList(){
        $result = array();

        /* Prefix Field */
        if($this->showPrefix()){
            $field = array();

            $field['label']   =    $this->getStoreLabel('prefix');
            $field['required'] = $this->isPrefixRequired();
            $field['name'] = $this->getFieldName('prefix');

            if($this->getPrefixOptions() === false){
                $field['type']  =  'text';
            }else{
                $field['type']  =  'select';
                $field['options'] = $this->getPrefixOptions();
            }
           
            $result[] = $field;
        }

        /* First Name Field */
        $field = array();
        $field['label'] = $this->getStoreLabel('firstname');
        $field['required'] = true;
        $field['name'] = $this->getFieldName('firstname');
        $field['type'] = 'text';
        $result[] = $field;

        /* Middle Name Field */
        if ($this->showMiddlename()){
            $field = array();
            $field['label'] = $this->getStoreLabel('middlename');
            $field['required'] = $this->isMiddlenameRequired();
            $field['name'] = $this->getFieldName('middlename');
            $field['type'] = 'text';
            $result[] = $field;
        }

         /* Last Name Field */
        $field = array();
        $field['label'] = $this->getStoreLabel('lastname');
        $field['required'] = true;
        $field['name'] = $this->getFieldName('lastname');
        $field['type'] = 'text';
        $result[] = $field;

        /* Suffix Field */
        if($this->showSuffix()){
            $field = array();

            $field['label']   =    $this->getStoreLabel('suffix');
            $field['required'] = $this->isSuffixRequired();
            $field['name'] = $this->getFieldName('suffix');

            if($this->getSuffixOptions() === false){
                $field['type']  =  'text';
            }else{
                $field['type']  =  'select';
                $field['options'] = $this->getSuffixOptions();
            }

            $result[] = $field;
        }
        return $result;
    }
}
?>