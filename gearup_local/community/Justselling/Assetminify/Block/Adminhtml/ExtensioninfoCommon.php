<?php

class Justselling_Assetminify_Block_Adminhtml_ExtensioninfoCommon extends Mage_Adminhtml_Block_System_Config_Form_Field {
    protected $_hasSelftest = false;
    protected $_idString = '';
    protected $_moduleName = '';

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setTemplate('assetminify/selftester.phtml');
        $this->setShowSelftestButton(false);
        if ($this->_hasSelftest) {
            if (Mage::getModel($this->_idString . '/selftester')) {
                $this->setShowSelftestButton(true);
                $this->setSelftestButtonUrl(
                    Mage::helper('adminhtml')->getUrl(
                        'adminhtml/selftester',
                        array(
                             'module'     => $this->_idString,
                             'moduleName' => $this->_moduleName
                        )
                    )
                );
                $element->setReadonly(true, true);
            }
        }
        $this->setConfigVersion((string)Mage::getConfig()->getModuleConfig($this->_moduleName)->version);

        return $this->_toHtml();
    }
}