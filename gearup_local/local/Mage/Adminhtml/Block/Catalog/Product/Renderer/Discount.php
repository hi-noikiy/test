<?php

class Mage_Adminhtml_Block_Catalog_Product_Renderer_Discount extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if ($row->getData('price') > 0 && $row->getData('special_price') > 0)
            return floor((100 * ($row->getData('price') - $row->getData('special_price')) ) / $row->getData('price')).'%';
        return;
    }

}
