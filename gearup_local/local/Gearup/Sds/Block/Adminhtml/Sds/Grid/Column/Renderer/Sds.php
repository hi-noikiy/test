<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Sds
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if ($row->getData('same_day_shipping') == 1) {
            $html = '<span id="sds'.$row->getId().'" class="sdsyesc'.$row->getId().'">'.Mage::helper('catalog')->__('Yes').'</span>';
        } else {
            $html = '<span id="sds'.$row->getId().'" class="sdsnoc'.$row->getId().'">'.Mage::helper('catalog')->__('No').'</span>';
        }
        $html .= "<script>
                    $$('.sdsyesc".$row->getId()."').each(function(s) {
                        var parentid = $(s).up(0);
                        parentid.addClassName('sdsyes');
                        parentid.writeAttribute('onclick', 'updateSds(". $row->getId() ."); return false');
                    });
                    $$('.sdsnoc".$row->getId()."').each(function(s) {
                        var parentid = $(s).up(0);
                        parentid.addClassName('sdsno');
                        parentid.writeAttribute('onclick', 'updateSds(". $row->getId() ."); return false');
                    });
                </script>";
        return $html;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        return $row->getData($this->getColumn()->getIndex());
    }

}
