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
class Gearup_Competera_Block_Adminhtml_Competera_Priceload_Renderer_CustomPrice extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $value = $row->getData($this->getColumn()->getIndex());
        $url = Mage::helper("adminhtml")->getUrl("*/*/customprice");        
        return '<input id="customprice-'.$row->getData('entity_id').'" type="text" value="' . $value . '" /><button type="submit" onclick="updateCompeteraSpecialPrice(document.getElementById(\'customprice-'.$row->getData('entity_id').'\').value,'.$row->getData('entity_id').',\''.$url.'\');">'. $this->helper('core')->__("Update") .'</button>';
    }

}
