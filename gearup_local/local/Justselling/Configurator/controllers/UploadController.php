<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_fileuploader
 * @copyright   Copyright (C)2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 */

class Justselling_Configurator_UploadController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function indexAction()
    {
    	Mage::Log("UploadController indexAction");
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        if ($block = $this->getLayout()->getBlock('configurator_myuploads')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Uploads'));
       
        $this->renderLayout();
    }
    
    public function uploadAction()
    {
    	Mage::Log("Fileuploader uploadAction");
    	 
    	$id = $this->getRequest()->getParam("id");
    	$file = $this->getRequest()->getParam("file");
    	$file = str_replace(" ","_",$file);
    
    	Mage::Log("id=".$id." file=".$file);
    	if ($id && $file) {
    		$upload = Mage::getModel('configurator/upload')->load($id);
    		Mage::Log("upload=".var_export($upload,true));
    		if ($upload) {
    			$upload->setFile($upload->getOrderId().DS.$file);
    			$upload->setStatus('1');
    			$upload->save();
    		}
    	}
    	 
    	$this->_redirect('*/*/index');
    }    
    
    public function deleteAction() 
    {
    	Mage::Log("UploadController resetAction");
    	
    	$id = $this->getRequest()->getParam("id");
    	
    	Mage::Log("id=".$id);
    	if ($id) {
    		$upload = Mage::getModel('configurator/upload')->load($id);
    		Mage::Log("upload=".var_export($upload,true));
    		if ($upload) {
    			$upload->setFile(NULL);
    			$upload->setStatus('0');
      			$upload->save();
    		}
    	}
    	
		$this->_redirect('*/*/index');
    }

}
