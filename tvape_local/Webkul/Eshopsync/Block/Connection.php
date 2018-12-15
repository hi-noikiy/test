<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Connection extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('eshopsync/button.phtml')->toHtml();
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
       return $this->_toHtml();
   }

   public function getButtonHtml() {
        $button = Mage::app()->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'wkTestButton',
            'label'     => $this->helper('adminhtml')->__('Test Connection')
        ));
        return $button->toHtml();
    }
}
