<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Inline
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getLowStock();
        if (!$value) {
            $color = '';
        } else {
            if (round($row->getQty()) >= $value && $row->getQty() != 0) {
                $color = 'background:#00e600;';
            } elseif (round($row->getQty()) < $value && $row->getQty() != 0) {
                $color = 'background:#ffff00;';
            } elseif (round($row->getQty()) < $value && $row->getQty() == 0) {
                $color = 'background:#ff0000;';
            }
        }
        $html = '<input style="width:30%;'.$color.'" type="text" id="qty'.$row->getId().'"';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . round($row->getData($this->getColumn()->getIndex()), 0) . '"';
        $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" />';
        //$html .= '<button style="margin-left:7px;" onclick="updateField(this, '. $row->getId() .'); return false">' . Mage::helper('gearup_sds')->__('Update') . '</button>';
        $html .= '<button id="button'.$row->getId().'" style="margin-left:7px;" onclick="opendescPopup('. $row->getId() .'); return false">' . Mage::helper('gearup_sds')->__('Update') . '</button>';
        $html .= '<input type="hidden" value="'.round($row->getData($this->getColumn()->getIndex()), 0).'"/>';
        return $html;
    }

}
