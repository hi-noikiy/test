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
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_Model_Mysql4_Template extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('configurator/template', 'id');
	}
	
	/**
	 * 
	 * link a template with a custom product option
	 * @param integer $productOptionId
	 * @param integer $templateId
	 * @return boolean
	 */
	public function linkTemplateWithProductOption($productOptionId,$templateId,$storeId=0)
    {    	
    	//Zend_Debug::dump($this->_connections);  exit;
    	
    	$select = $this->_getReadAdapter()->select();
		$productOptionTemplateTable = Mage::getSingleton("core/resource")->getTableName('configurator/product_option_template');

    	if( $storeId != 0) {
    		$sql = $select->from(array('pot' => $productOptionTemplateTable))
    			->where('pot.catalog_product_option_option_id = ?',$productOptionId)
    			->where('pot.store_id = ?',$storeId);
    	} else {
    		$sql = $select->from(array('pot' => $productOptionTemplateTable))
    			->where('pot.catalog_product_option_option_id = ?',$productOptionId);
    	}
    	
    		
    	$result = $this->_getReadAdapter()->fetchAll($sql);
    	
    	//Zend_Debug::dump($result); exit;
    	
    	if( !$result )
    	{
    		$result = $this->_getWriteAdapter()->insert($productOptionTemplateTable, array(
    			'catalog_product_option_option_id' => $productOptionId,
    			'conf_template_id' => $templateId,
    			'store_id' => $storeId
    		));
    		
    		if( $result )
    		{    			
    			return true;
    		} 
    		else 
    		{
    			return false;
    		}
    	} else {
    		
    		foreach($result as $row)
    		{
    			
    			if( $row['conf_template_id'] != $templateId )
    			{
    				$result = $this->_getWriteAdapter()->update($productOptionTemplateTable, array(
    					'conf_template_id' => $templateId
    				),'conf_template_id = '.$row['conf_template_id'].' AND catalog_product_option_option_id = '.$row['catalog_product_option_option_id'].' AND store_id = '.$storeId );
    				    				
		    		if( $result )
		    		{    			
		    			return true;
		    		} 
		    		else 
		    		{
		    			return false;
		    		}
    			}
    		}
    	}
    	
    	return true;
    }
    
    public function getLinkedTemplateId($productOptionId,$storeId=0)
    {
    	$select = $this->_getReadAdapter()->select();
		$productOptionTemplateTable = Mage::getSingleton("core/resource")->getTableName('configurator/product_option_template');

    	$sql = $select->from(array('pot' => $productOptionTemplateTable))
    		->where('pot.catalog_product_option_option_id = ?',$productOptionId)
    		->where('pot.store_id = ?',$storeId);
    	$result = $this->_getReadAdapter()->fetchRow($sql);
    	
    	//Zend_Debug::dump($result);
    	
    	if( !$result ) {
    		$select2 = $this->_getReadAdapter()->select();    	
    	
	    	$sql = $select2->from(array('pot' => $productOptionTemplateTable))
	    		->where('pot.catalog_product_option_option_id = ?',$productOptionId);
	    	$result = $this->_getReadAdapter()->fetchRow($sql);
	    	//Zend_Debug::dump($result);
    	}
    	
    	return $result['conf_template_id'];
    }
    
	public function removeTemplateFromProductOption($productOptionId,$templateId,$storeId=0)
    {
  		
    	$select = $this->_getReadAdapter()->select();
		$productOptionTemplateTable = Mage::getSingleton("core/resource")->getTableName('configurator/product_option_template');
    	
    	$sql = $select->from(array('pot' => $productOptionTemplateTable))
    		->where('pot.catalog_product_option_option_id = ?',$productOptionId)
    		->where('pot.store_id = ?',$storeId);
    	$result = $this->_getReadAdapter()->fetchRow($sql);
    	
    	if( !$result ) return false;
    	
    	$result = $this->_getWriteAdapter()->delete(
			$productOptionTemplateTable,
    		"catalog_product_option_option_id = $productOptionId AND conf_template_id = $templateId AND store_id = $storeId"
    	);

    	if( $result ) return true;
    	
    	return false;
    }
    
    public function getLinkedProducts($templateId, $storeId=0) {
    	$select = $this->_getReadAdapter()->select();
		$productOptionTemplateTable = Mage::getSingleton("core/resource")->getTableName('configurator/product_option_template');
    	
    	$sql = $select->from(array('pot' => $productOptionTemplateTable))
    		->where('pot.conf_template_id = ?',$templateId)
    		->where('pot.store_id = ?',$storeId);
    		
    		$products = array();
    		$results = $this->_getReadAdapter()->fetchAll($sql);
    		foreach ($results as $result) {
    			$option = Mage::getModel("catalog/product_option")->load($result['catalog_product_option_option_id']);
    			if ($option)
    				$products[$option->getProductId()] = $result['catalog_product_option_option_id'];
    		}

    	return $products;
    }
    
}