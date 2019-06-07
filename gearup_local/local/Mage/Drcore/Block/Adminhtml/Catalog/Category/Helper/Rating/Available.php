<?php

class Mage_Drcore_Block_Adminhtml_Catalog_Category_Helper_Rating_Available extends Varien_Data_Form_Element_Multiselect
{
    /**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
        $disabled = !($this->hasData('value') && $this->getData('value') !== null);
        if ($disabled) {
            $this->setData('disabled', 'disabled');
        }
        $html = parent::getElementHtml();
        
        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $store = Mage::getModel('core/store')->load($storeId);
            
            if ($store && $store->getId()) {
                if ($store->getRootCategoryId() != Mage::app()->getRequest()->getParam('id')) {
                    $htmlId = 'use_config_' . $this->getHtmlId();
                    $html .= '<input id="'.$htmlId.'" name="use_config[]" value="' . $this->getId() . '"';
                    $html .= ($disabled ? ' checked="checked"' : '');
    
                    if ($this->getReadonly()) {
                        $html .= ' disabled="disabled"';
                    }
                    $html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox" />';
    
                    $html .= ' <label for="'.$htmlId.'" class="normal">'
                        . Mage::helper('core')->__('Use Parent Category Settings').'</label>';
                }
            }
        }

        return $html;
    }
}
