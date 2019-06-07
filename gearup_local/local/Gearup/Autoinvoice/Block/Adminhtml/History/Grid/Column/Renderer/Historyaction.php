<?php
class Gearup_Autoinvoice_Block_Adminhtml_History_Grid_Column_Renderer_Historyaction
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        return $row->getActions();
    }

}
