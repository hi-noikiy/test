<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Price
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $html = '<div id="edit_price '.$row->getId().'" class="edit_price"><input style="width:50%;" type="text" id="price'.$row->getId().'"';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . number_format((float)$row->getData($this->getColumn()->getIndex()), 2, '.', '') . '"';
        $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" /></div>';
        return $html;
    }

}
