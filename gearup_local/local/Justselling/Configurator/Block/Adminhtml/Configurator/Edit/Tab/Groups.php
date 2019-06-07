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
 
class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Groups extends Mage_Adminhtml_Block_Widget
{
	protected $_data = null;
	
	public function __construct()
	{
		parent::__construct();
        $this->setTemplate('configurator/template/groups.phtml');    

        $this->_data = Mage::registry("configurator_data")->getData();;
	}
	
	protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Group'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_group'
                ))
        );
     
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Group'),
                    'class' => 'delete delete-option-group '
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

    public function getGroupValues()
    {   			
		if( is_null($this->getTemplateId()) ) return null;
    	
    	$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $optionGroupTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_group');

		$select = $connection->select()
			->from(
				array("co" => $optionGroupTable),
				array("id","template_id","title","sort_order","group_image")
			)
			->where('template_id = ?',$this->_data['id'])
			->order(array("co.sort_order ASC","co.id ASC"));
			
		$items = $connection->fetchAll($select);		
		
		$items = array_values($items);		
		// Zend_Debug::dump($items); exit;		
		return $items;
    }
    
   	public function getLastGroupId()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $optionGroupTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_group');
	
		$select = $connection->select()
			->from(array("co" => $optionGroupTable),array("MAX(id)"));
			
		$id = $connection->fetchOne($select);
		
		if($id) return $id;
		
		return 0;
	}
	
	public function getFieldName()
    {
    	return "template[groups]";
    }
    
    public function getFieldId()
    {
    	return "template_group";
    }

    protected function _afterToHtml($html)
    {
        $block = Mage::helper('configurator')->getHelpIqLink(
            $this,
            "helpiq-lightbox",
            Mage::helper('configurator')->__('optiongroups'),
            Mage::helper('configurator')->__('Option groups')
        );
        return  $block.$html;
    }
    
}