<?php
class Onesaas_Connect_Adminhtml_Osconnect_AdminonesaasController extends Mage_Adminhtml_Controller_Action{

	public function indexAction(){
		$this->loadLayout();
		$this->renderLayout();
	}

	public function saveAction(){
		if ($data = $this->getRequest()->getPost()) {
			$id = $this->getRequest()->getPost('id');

			if(!empty($id)){
				$model = Mage::getModel('osconnect/osconnect')->setData($data)->setId($id);
			}else{
				$model = Mage::getModel('osconnect/osconnect')->setData($data);
			}

			try{
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('osconnect')->__('Key Successfully Saved'));
				$this->getResponse()->setRedirect($this->getUrl('*/*/'));
				return;
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->getResponse()->setRedirect($this->getUrl('*/*/'));
		return;
    }

	public function keyGenAction(){
		$newkey = md5(mt_rand()).md5(mt_rand());
		echo $newkey;
	}
}
?>
