<?php
class Hatimeria_OrderManager_Block_Adminhtml_Period_Grid_Column_Renderer_Datefrom
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $dateFrom = new DateTime($row->getDateFrom());
        $dateFrom = $dateFrom->format('d-m-y H:i:s');
        return $dateFrom;
    }

}
