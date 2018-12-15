<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Folder extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/folder');
    }

    public function fetchSalesforceFolders($client)
    {
    	$status = false;
    	if($client){
            $folders = Mage::helper('eshopsync')->getFolderBySOAPClient($client);
            if($folders){
                $jsonData = Mage::helper('core')->jsonEncode($folders);
                Mage::getModel('core/config')->saveConfig('eshopsync/default/salesforce_folders', $jsonData);
                $status = true;
            }
    	}
    	return $status;
    }

}
