<?php
class EM_LayeredNavigation_Adminhtml_ImageController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Handle ajax image upload
	 */
	public function addAction() {
		$helper = Mage::helper('layerednavigation');
		$qqfile = $this->getRequest()->getParam('qqfile');
		$saveName = $helper->getImageSaveName($qqfile);
		$filePath = $helper->getImagePath($saveName);
		
		file_put_contents($filePath, file_get_contents('php://input'));
		$result = array(
			'url' => $helper->getImageUrl($saveName),
			'fileName' => $saveName
		);

		$this->getResponse()
			->setHeader('Content-type', 'application/json')
			->setBody(json_encode($result));
	}
	
	public function removeAction() {
		throw new Exception('not implemented');
	}
	
}