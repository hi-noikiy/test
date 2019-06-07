<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NewPrice
 *
 * @author
 */
class Gearup_Competera_Block_Adminhtml_Competera_Priceload_Renderer_NewPrice extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  
    public function render(Varien_Object $row) {
        $min_price_custom = $row->getData('min_price_custom');
        $value = $row->getData($this->getColumn()->getIndex());
        
      
        if ($min_price_custom > $value)
            return '<span style="color:red;">' . Mage::app()->getLocale()->currency('USD')->toCurrency($value) . '</span>';

        return Mage::app()->getLocale()->currency('USD')->toCurrency($value);
    }

}
