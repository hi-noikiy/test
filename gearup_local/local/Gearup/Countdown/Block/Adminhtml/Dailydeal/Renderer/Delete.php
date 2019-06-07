<?php
class Gearup_Countdown_Block_Adminhtml_Dailydeal_Renderer_Delete extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $message = $this->__('Are you sure you want to delete this Deal?');
        $delete = $this->getUrl('*/*/delete',array('id' => $row->getId()));
        $link = '<a onclick="confirmSetLocation('."'$message'".','."'$delete'".')" >Delete</a>';
        return $link;
    }

}
