<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 */

/**
 * @method string getSessionId()
 * @method Justselling_Configurator_Model_Upload setSessionId(string $value)
 * @method string getStatus()
 * @method Justselling_Configurator_Model_Upload setStatus(string $value)
 * @method string getJsTemplateId()
 * @method Justselling_Configurator_Model_Upload setJsTemplateId(string $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Upload setOptionId(int $value)
 * @method int getOrderId()
 * @method Justselling_Configurator_Model_Upload setOrderId(int $value)
 * @method int getFileuploaderTemplateId()
 * @method Justselling_Configurator_Model_Upload setFileuploaderTemplateId(int $value)
 * @method int getQuoteItemId()
 * @method Justselling_Configurator_Model_Upload setQuoteItemId(int $value)
 * @method int getId()
 * @method Justselling_Configurator_Model_Upload setId(int $value)
 * @method int getMinDpiy()
 * @method Justselling_Configurator_Model_Upload setMinDpiy(int $value)
 * @method int getMinDpix()
 * @method Justselling_Configurator_Model_Upload setMinDpix(int $value)
 * @method int getOrderItemId()
 * @method Justselling_Configurator_Model_Upload setOrderItemId(int $value)
 * @method int getFileuploaderProductId()
 * @method Justselling_Configurator_Model_Upload setFileuploaderProductId(int $value)
 * @method string getFile()
 * @method Justselling_Configurator_Model_Upload setFile(string $value)
 * @method string getCreatedAt()
 * @method Justselling_Configurator_Model_Upload setCreatedAt(string $value)
 * @method int getQuoteId()
 * @method Justselling_Configurator_Model_Upload setQuoteId(int $value)
 * @method int getCustomerId()
 * @method Justselling_Configurator_Model_Upload setCustomerId(int $value)
 */
class Justselling_Configurator_Model_Upload extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/upload');
	}
	
	public function loadByOrderId($order_id)
	{
		Mage::Log("loadByOrderId ".$order_id);
		$orders = Mage::getModel('configurator/upload')->getCollection();
    	$orders->addFieldToFilter('order_id',array('eq'=>$order_id));
    	foreach ($orders as $order) {
    		$this->load($order->getId());
    		return $this;
    	}
    	return $this;
    }	
    
    public function getOpenUploadsByOrderId($order_id) {
    	Mage::Log("getOpenUploadsByOrderId ".$order_id);
    	$uploads = Mage::getModel('configurator/upload')->getCollection();
    	$uploads->addFieldToFilter('order_id',$order_id);
    	$uploads->addFieldToFilter('status',0);
    	if ($uploads->getFirstItem()->getId()) {
    		$this->load($uploads->getFirstItem()->getId());
    		Mage::Log("found item ".$uploads->getFirstItem()->getId());
    		return true;
    	}
    	Mage::Log("no upload");
    	return false;
    }
    
    public function getProductName($upload) {
    	if ($upload and $upload->getOrderItemId()) {
    		$order_item = Mage::getModel("sales/order_item")->load($upload->getOrderItemId());
    		$_product = Mage::getModel("catalog/product")->load($order_item->getProductId());
    		if ($_product)
    			return $_product->getName();
    		return false;
    	}
    	return false;
    }
    
    public function getProductSku($upload) {
    	if ($upload and $upload->getOrderItemId()) {
    		$order_item = Mage::getModel("sales/order_item")->load($upload->getOrderItemId());
    		$_product = Mage::getModel("catalog/product")->load($order_item->getProductId());
    		if ($_product)
    			return $_product->getSku();
    		return false;
    	}
    	return false;
    }
}