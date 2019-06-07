<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_OrderQty
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        if (round($row->getData('low_stock'), 0) > round($row->getQty(), 0)) {
            $ordeQty =  round($row->getData('low_stock'), 0) - (round($row->getQty(), 0) + round($row->getInbound(), 0));
        } else {
            $ordeQty =  round($row->getData('low_stock'), 0);
        }
        if ($ordeQty == 0) {
            $ordeQty = (string) 0;
        }
        $html = '<input style="width:80%;" type="text" id="orderqty'.$row->getId().'" attr-id="'.$row->getId().'"';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . $ordeQty . '"';
        $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" />';
        return $html;
    }

}
