<?php 
class EM_Addphone_Block_Adminhtml_Customer_Grid_Renderer_Test
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
	public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
		$customer_data = Mage::getModel('customer/customer')->load($value);
		$phone=$customer_data->getData('mobile');
		return $phone;
	}
}
?>