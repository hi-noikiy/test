<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_category';
    $this->_blockGroup = 'eshopsync';
    $this->_headerText = Mage::helper('eshopsync')->__('Salesforce Category Mapping');

    $this->_addButton('viewall', array(
        'label'     => Mage::helper('eshopsync')->__('View All'),
        'onclick'   => 'setLocation(\'' . $this->getViewAll() .'\')',
        'class'     => '',
    ));

    $this->_addButton('sync', array(
        'label'     => Mage::helper('eshopsync')->__('Synchronised ('.Mage::helper("eshopsync/data")->getSyncNumber("category").')'),
        'onclick'   => 'setLocation(\'' . $this->getShowSyncUrl() .'\')',
        'class'     => '',
    ));

    $this->_addButton('unsync', array(
          'label'     => Mage::helper('eshopsync')->__('Unsynchronised ('.$this->getUnsyncNumber().')'),
          'onclick'   => 'setLocation(\'' . $this->getShowUnsyncUrl() .'\')',
          'class'     => '',
    ));

    $this->_addButtonLabel = Mage::helper('eshopsync')->__('Manual Category Mapping');
  	$this->_addButton('eshopsync_export_category', array(
      	'label'   => Mage::helper('eshopsync')->__('Export All Categories'),
      	'class'   => 'eshopsync_category_export save'
    ));

    $this->_addButton('eshopsync_update_category', array(
        'label'   => Mage::helper('eshopsync')->__('Update All Categories'),
        'class'   => 'eshopsync_category_update save'
    ));
    parent::__construct();
    $this->_removeButton("add");

  }

  public function getShowSyncUrl(){
		return $this->getUrl('*/*/index/sync/1');
	}

  public function getShowUnsyncUrl(){
    return $this->getUrl('*/*/index/unsync/1');
  }

  public function getViewAll(){
    return $this->getUrl('*/*/index/');
  }

  public function getUnsyncNumber(){
    $collection = Mage::getModel('catalog/category')->getCollection();
    $prefix = Mage::getConfig()->getTablePrefix();
    $collection->getSelect()->joinLeft(
      array('cat' => $prefix.'wk_salesforce_eshopsync_category_mapping'),
      'cat.magento_id = e.entity_id',
      array('magento_id','sforce_id','created_by','created_at','need_sync','error_hints')
    );
    $collection->addAttributeToFilter('entity_id',array('nin' => 1));
    $collection->getSelect()->where("cat.error_hints is not null or cat.magento_id is null");
    $n = count($collection);
    return $n;
  }

}
