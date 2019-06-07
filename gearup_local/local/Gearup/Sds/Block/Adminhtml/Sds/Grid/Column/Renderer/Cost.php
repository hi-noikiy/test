<?php

/* Ticket5492 - DXB Storage Manager - Report In/Out 
 */
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Cost
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getLowStock();
        $html = '<input style="width:50%;" type="text" id="cost'.$row->getId().'"';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        if ($row->getData($this->getColumn()->getIndex()) == '') {
            $value = '0';
        } else {
            $value = round($row->getData($this->getColumn()->getIndex()), 2);
        }
        $html .= 'value="' . $value . '"';
        $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" />';
        // $html .= '<button style="margin-left:7px;" onclick="updateCost(this, '. $row->getId() .'); return false">' . Mage::helper('gearup_sds')->__('Update') . '</button>';
        $html .= '<input type="hidden" value="'.$value.'"/>';
        return $html;
    }
}