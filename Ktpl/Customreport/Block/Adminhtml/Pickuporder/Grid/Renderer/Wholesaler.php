<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Wholesaler extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
        $url = $this->getUrl('*/*/updatePickupaddress', ['_current' => true, '_use_rewrite' => true]);
        $rowval = $row->getData($this->getColumn()->getIndex());

 		$html = '<select id="wholesaler'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '" onchange="updatePickupaddress(this, '. $row->getPickupId() .', \''. $url .'\'); return false" style="width:70px;">';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $wholesalers = $objectManager->create('Ktpl\Customreport\Model\Wholesaler')->getCollection();

    	$html .= '<option value=""></option>';
        foreach ($wholesalers as $wholesaler) {
            $selval = "";
            if($wholesaler->getId() == $rowval) { $selval = 'selected="selected"'; }
            $html .= '<option value="'. $wholesaler->getId() .'" '.$selval.'>'. $wholesaler->getName() .'</option>';
        }
        $html .= '</select>';

 
        return $html;
    }
}