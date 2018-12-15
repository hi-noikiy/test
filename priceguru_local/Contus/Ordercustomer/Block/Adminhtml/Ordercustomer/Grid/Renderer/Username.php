<?php


class Contus_Ordercustomer_Block_Adminhtml_Ordercustomer_Grid_Renderer_Username extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get Payment type by order id
 */
    public function render(Varien_Object $row) {          	      
		    $order_id = $row->getData('order_id');
		    $resource       = Mage::getSingleton('core/resource');
		    $readConnection = $resource->getConnection('core_read');
		    $select = $readConnection->select()
					    ->from('ordercustomer_payment', array('*')) 
					    ->where('order_id=?',$order_id);              	 
			$rowArray =$readConnection->fetchAll($select);
			$user_name = "-";
			foreach ($rowArray as $username){
				$user_name = $username['username'];
			}
			return $user_name;
    }
}



