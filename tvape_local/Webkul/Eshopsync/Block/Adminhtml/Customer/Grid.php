<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('eshopsyncGrid');
      $this->setDefaultSort('e.entity_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('customer/customer')->getCollection();
      $prefix = Mage::getConfig()->getTablePrefix();
      $collection->getSelect()->joinLeft(
          array('cus' => $prefix."wk_salesforce_eshopsync_customer_mapping"),
          'cus.magento_id = e.entity_id',
          array('magento_id','sforce_id','created_by','created_at','error_hints')
      );

      if($this->getRequest()->getParam('sync') == 1){
        $collection->getSelect()->where("cus.error_hints is null and cus.magento_id is not null");
      }
      elseif($this->getRequest()->getParam('unsync') == 1){
        $collection->getSelect()->where("cus.error_hints is not null or cus.magento_id is null");
      }

      $this->setCollection($collection);
      return parent::_prepareCollection();

  }

  protected function _prepareColumns()
  {
      $this->addColumn('entity_id', array(
          'header'    => Mage::helper('eshopsync')->__('ID'),
          'align'     =>'center',
          'width'     => '100px',
          'filter_index' => 'e.entity_id',
          'index'     => 'entity_id',
      ));


      $this->addColumn('email',array(
          'header'  =>Mage::helper('eshopsync')->__('Customer Email'),
          'align'   =>'center',
          'index'   =>'email',
      ));


      $this->addColumn('magento_id',array(
        'header'  =>Mage::helper('eshopsync')->__('Magento Customer Id'),
        'align'   =>'center',
        'index'   =>'magento_id',
      ));

      $this->addColumn('sforce_id', array(
          'header'    => Mage::helper('eshopsync')->__('Salesforce Account Id'),
          'align'     =>'center',
          'index'     => 'sforce_id',
        ));

      $this->addColumn('created_by', array(
          'header'    => Mage::helper('eshopsync')->__('Created By'),
          'align'     =>'center',
          'index'     => 'created_by',
      ));

      $this->addColumn('created_at', array(
          'header'    => Mage::helper('eshopsync')->__('Created At'),
          'align'     =>'center',
          'index'     => 'created_at',
      ));

      $this->addColumn('linkContacts', array(
          'header'    => Mage::helper('eshopsync')->__('Click to view Contact'),
          'align'     =>'center',
          'index'     => 'linkContacts',
          'filter' => false,
          'renderer' => 'Webkul_Eshopsync_Block_Adminhtml_Renderer_Customer'
      ));

      $this->addColumn('link', array(
          'header'    => Mage::helper('eshopsync')->__('Status'),
          'align'     =>'center',
          'index'     => 'link',
          'filter' => false,
          'renderer' => 'Webkul_Eshopsync_Block_Adminhtml_Renderer_DisplayError'
      ));

  		$this->addExportType('*/*/exportCsv', Mage::helper('eshopsync')->__('CSV'));
  		$this->addExportType('*/*/exportXml', Mage::helper('eshopsync')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('magento_id');
        $this->getMassactionBlock()->setFormFieldName('eshopsync');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('eshopsync')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('eshopsync')->__('Are you sure?')
        ));
        return $this;
    }
}
