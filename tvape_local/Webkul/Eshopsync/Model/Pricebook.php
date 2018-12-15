<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Pricebook extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/pricebook');
    }

    public function fetchSalesforcePricebooks($client)
    {
    	$status = false;
    	if($client){
            $pricebooks = Mage::helper('eshopsync')->getPriceBookBySOAPClient($client);
            if($pricebooks){
                $jsonData = Mage::helper('core')->jsonEncode($pricebooks);
                Mage::getModel('core/config')->saveConfig('eshopsync/default/salesforce_pricebooks', $jsonData);

                $standard_pricebook = Mage::helper('eshopsync')->getStandardPriceBookBySOAPClient($client);
                if($standard_pricebook){
                    Mage::getModel('core/config')->saveConfig('eshopsync/default/standard_pricebook', $standard_pricebook);
                }
                $status = true;
            }
        }
    	return $status;
    }
}
