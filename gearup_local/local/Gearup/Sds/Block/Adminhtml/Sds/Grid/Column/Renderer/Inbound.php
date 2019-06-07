<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Inbound
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        if ($this->getRequest()->getParam('inbound_filter')) {
            $html = '<input style="width:80%;" type="text" id="inbound'.$row->getId().'" attr-id="'.$row->getId().'"';
            $html .= 'name="' . $this->getColumn()->getId() . '" ';
            $html .= 'value="' . $row->getInbound() . '"';
            $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" />';
        } else {
            $html = $row->getInbound();
        }
        return $html;
    }

}
