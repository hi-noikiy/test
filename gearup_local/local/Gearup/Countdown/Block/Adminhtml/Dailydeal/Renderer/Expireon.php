<?php
class Gearup_Countdown_Block_Adminhtml_Dailydeal_Renderer_expireon extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return date('d M Y H:i:s', strtotime($row->getData($this->getColumn()->getIndex())));
    }

}
