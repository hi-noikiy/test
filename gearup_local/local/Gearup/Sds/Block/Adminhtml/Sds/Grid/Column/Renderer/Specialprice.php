<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Specialprice
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        if (round($row->getData($this->getColumn()->getIndex()))) {
            $html = '<div id="sprice '.$row->getId().'" class="sprice" onclick="editspline('. $row->getId() .'); return false">'.Mage::app()->getLocale()->currency('USD')->toCurrency($row->getData($this->getColumn()->getIndex())).'</div>';
            $html .= '<div id="edit_sprice '.$row->getId().'" class="edit_sprice"><input style="width:50%;" type="text" id="sprice'.$row->getId().'"';
            $html .= 'name="' . $this->getColumn()->getId() . '" ';
            $html .= 'value="' . number_format((float)$row->getData($this->getColumn()->getIndex()), 2, '.', '') . '"';
            $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" />';
//            $html .= '<button style="margin-left:7px;" onclick="updateSprice(this, '. $row->getId() .'); return false">' . Mage::helper('gearup_sds')->__('Update') . '</button>';
//            $html .= '<input type="hidden" value="'.$row->getData($this->getColumn()->getIndex()).'"/><div>';
        } else {
            $html = '<div id="sprice '.$row->getId().'" class="sprice no" onclick="editspline('. $row->getId() .'); return false">'.Mage::app()->getLocale()->currency('USD')->toCurrency($row->getData($this->getColumn()->getIndex())).'</div>';
            $html .= '<div id="edit_sprice '.$row->getId().'" class="edit_sprice no"><input style="width:50%;" type="text" id="sprice'.$row->getId().'"';
            $html .= 'name="' . $this->getColumn()->getId() . '" ';
            $html .= 'value="' . $row->getData($this->getColumn()->getIndex()) . '"';
            $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" />';
//            $html .= '<button style="margin-left:7px;" onclick="updateSprice(this, '. $row->getId() .'); return false">' . Mage::helper('gearup_sds')->__('Update') . '</button>';
//            $html .= '<input type="hidden" value="'.$row->getData($this->getColumn()->getIndex()).'"/><div>';
        }
        $html .= "<script>$$('.sprice').each(function(s) {
                    var parentid = $(s).up(0);
                    parentid.setStyle({width: '1%'});
                });</script>";
        return $html;
    }

}
