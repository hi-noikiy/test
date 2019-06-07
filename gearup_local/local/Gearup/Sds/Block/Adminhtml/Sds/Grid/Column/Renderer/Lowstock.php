<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Lowstock
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getLowStock();
        $html = '<input style="width:30%;" type="text" id="lowstock'.$row->getId().'"';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        if ($row->getData($this->getColumn()->getIndex()) == '') {
            $value = '';
        } else {
            $value = round($row->getData($this->getColumn()->getIndex()), 0);
        }
        $html .= 'value="' . $value . '"';
        $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" />';
        $html .= '<button style="margin-left:7px;" onclick="updateLowS(this, '. $row->getId() .'); return false">' . Mage::helper('gearup_sds')->__('Update') . '</button>';
        $html .= '<input type="hidden" value="'.$value.'"/>';
        return $html;
    }

}
