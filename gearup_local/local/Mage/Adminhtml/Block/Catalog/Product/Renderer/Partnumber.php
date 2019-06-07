<?php

class Mage_Adminhtml_Block_Catalog_Product_Renderer_Partnumber extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $value = $row->getData($this->getColumn()->getIndex());
        if ($row->getData('dxbsp') == 1) {
            $html = '<span id="dxbsp'.$row->getId().'" class="dxbspyes'.$row->getId().'">'.$value.'</span>';
        } else {
            $html = '<span id="dxbsp'.$row->getId().'" class="dxbspno'.$row->getId().'">'.$value.'</span>';
        }
        $html .= "<script>
                    $$('.dxbspyes".$row->getId()."').each(function(s) {
                        var parentid = $(s).up(0);
                        parentid.addClassName('yellow');
                    });
                </script>";
        return $html;
    }

}
