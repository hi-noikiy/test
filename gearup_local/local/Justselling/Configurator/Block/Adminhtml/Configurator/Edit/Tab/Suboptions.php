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
**/

class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Suboptions extends Mage_Adminhtml_Block_Widget
{
	protected $_data = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('configurator/template/suboptions.phtml');    

        $this->_data = Mage::registry("configurator_data")->getData();;
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Section'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_sub_group'
                ))
        );
     
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Section'),
                    'class' => 'delete delete-suboption'
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

    public function getParentSelectHtml()
    {
        //Zend_Debug::dump( $this->getOptions()->toOptionArray() );  
        
        $options = Mage::getSingleton("configurator/option")->toOptionArray($this->getTemplateId());
        //print_r($options); exit;
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_option_id',
                'class' => 'select select-product-option-option_id required-option-select',
            ))
            ->setName($this->getFieldName().'[${id}][option_id]')
            ->setOptions($options);           
           
        return  $select->getHtml();
    }

    public function getSuboptionValues()
    {               
        if( is_null($this->getTemplateId()) ) return null;
        
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $optionGroupTable = Mage::getSingleton("core/resource")->getTableName('configurator_subsection');

        $select = $connection->select()
            ->from(
                array("co" => $optionGroupTable),
                array("id","template_id","option_id","sortorder","subtitle")
            )
            ->where('template_id = ?',$this->_data['id'])
            ->order(array("co.id DESC"));
            
        $items = $connection->fetchAll($select);        
        
        $items = array_values($items);      
        // Zend_Debug::dump($items); exit;      
        return $items;
    }
    
    public function getLastSuboptionId()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $optionGroupTable = Mage::getSingleton("core/resource")->getTableName('configurator_subsection');
    
        $select = $connection->select()
            ->from(array("co" => $optionGroupTable),array("MAX(id)"));
            
        $id = $connection->fetchOne($select);
        
        if($id) return $id;
        
        return 0;
    }
    
    public function getFieldName()
    {
        return "template[subsections]";
    }
    
    public function getFieldId()
    {
        return "template_subsection";
    }

    protected function _afterToHtml($html)
    {
        $block = Mage::helper('configurator')->getHelpIqLink(
            $this,
            "helpiq-lightbox",
            Mage::helper('configurator')->__('subsection'),
            Mage::helper('configurator')->__('Sub sections')
        );
        return  $block.$html;
    }
    
}