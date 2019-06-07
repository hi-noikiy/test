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
 
class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Postpricerules extends Mage_Adminhtml_Block_Widget
{
	protected $_data = null;
	
	public function __construct()
	{
		parent::__construct();
        $this->setTemplate('configurator/template/postpricerules.phtml');    

        $this->_data = Mage::registry("configurator_data")->getData();
	}
	
	protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Post Price Rule'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_postpricerule'
                ))
        );
     
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Post Price Rule'),
                    'class' => 'delete delete-postpricerule '
                ))
        );
        

        return parent::_prepareLayout();
    }
    
	public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
    
    
	public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
    
    public function getTemplateData()
    {
    	return $this->_data;
    }
    
    public function getTemplateId()
    {    	
    	if( isset($this->_data['id']) ) return $this->_data['id'];    	
    	return null;
    }    

    public function getPostpriceruleValues()
    {   			
		if( is_null($this->getTemplateId()) ) return null;
    	
    	$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $postPriceRulesTable = Mage::getSingleton("core/resource")->getTableName('configurator/postpricerule');
		
		$select = $connection->select()
			->from(
				array("co" => $postPriceRulesTable),
				array("id","template_id","title","post_price_rule","order")
			)
			->where('template_id = ?',$this->_data['id'])
			->order(array("co.order ASC","co.id ASC"));
			
		$items = $connection->fetchAll($select);		
		
		$items = array_values($items);		
		// Zend_Debug::dump($items); exit;		
		return $items;
    }
    
   	public function getLastPostpriceruleId()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $postPriceRulesTable = Mage::getSingleton("core/resource")->getTableName('configurator/postpricerule');
	
		$select = $connection->select()
			->from(array("co" => $postPriceRulesTable),array("MAX(id)"));
			
		$id = $connection->fetchOne($select);
		
		if($id) return $id;
		
		return 0;
	}
	
	public function getFieldName()
    {
    	return "template[postpricerule]";
    }
    
    public function getFieldId()
    {
    	return "template_postpricerule";
    }

    protected function _afterToHtml($html)
    {
        $block = Mage::helper('configurator')->getHelpIqLink(
            $this,
            "helpiq-lightbox",
            Mage::helper('configurator')->__('postprice-rules'),
            Mage::helper('configurator')->__('Postprice Rules')
        );
        return  $block.$html;
    }
    
}