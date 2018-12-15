<?php

class EM_Productsfilterwidget_WidgetController extends Mage_Core_Controller_Front_Action
{
	public function formAction()
	{
		$data = $this->getRequest()->getPost();
		$rule = Mage::getModel('salesrule/rule');
        $rule->loadPost($data['rule']);
                
        if ($rule->getActions()) {
            $rule->setActionsSerialized(serialize($rule->getActions()->asArray()));
            $rule->unsActions();
        }
		$tam	=	$rule->getActionsSerialized();
		$data	=	Mage::helper('core')->urlEncode($tam);
		
		$time	=	getdate();	
		
		$model = Mage::getModel('productsfilterwidget/productsfilterwidget');
		$model->setData(array('title'=>$time[0],'content'=>$data))->save();
		
        echo $data.'-'.$time[0];
	}
	
}