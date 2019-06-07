<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Partnumber
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if ($row->getData('dxbsp') == 1) {
            $html = '<span id="dxbsp'.$row->getId().'" class="dxbspyes'.$row->getId().'">'.$value.'</span>';
        } else {
            $html = '<span id="dxbsp'.$row->getId().'" class="dxbspno'.$row->getId().'">'.$value.'</span>';
        }
        $html .= "<script>
                    $$('.dxbspyes".$row->getId()."').each(function(s) {
                        var parentid = $(s).up(0);
                        parentid.addClassName('yellow');
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
