<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Stock
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if ($value) {
            $html = '<span id="stock'.$row->getId().'">In Stock</span>';
        } else {
            $html = '<span class="outofs" id="stock'.$row->getId().'">Out of Stock</span>';
        }
        $html .= "<script>$$('.outofs').each(function(s) {
                    var parentid = $(s).up(0);
                    parentid.addClassName('lowco');
                });</script>";
        return $html;
    }

}
