<?php
class Gearup_Shippingffdx_Block_Adminhtml_Reporthistory_Grid_Column_Renderer_Historyaction
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        return $row->getActions();
    }

}
