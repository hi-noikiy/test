<?php
class Hatimeria_OrderManager_Block_Adminhtml_Period_Grid_Column_Renderer_Dateto
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $dateTo = new DateTime($row->getDateTo());
        $dateTo = $dateTo->format('d-m-y H:i:s');
        return $dateTo;
    }

}
