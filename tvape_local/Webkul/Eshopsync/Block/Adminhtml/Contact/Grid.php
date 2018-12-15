<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Contact_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('contactGrid');
      $this->setDefaultSort('entity_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $id = $this->getRequest()->getParam('id');
      $collection = Mage::getModel('eshopsync/contact')->getCollection();
      $prefix = Mage::getConfig()->getTablePrefix();
      $collection->getSelect()->joinLeft(
            array('cus' => $prefix.'customer_entity'),
            'cus.entity_id = main_table.customer_id',
            array('email')
      );
      $collection->addFieldToFilter('customer_id',$id);
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('entity_id', array(
          'header'    => Mage::helper('eshopsync')->__('ID'),
          'align'     =>'center',
          'width'     => '100px',
          'filter_index' => 'main_table.entity_id',
          'index'     => 'entity_id',
      ));


      $this->addColumn('email',array(
          'header'  =>Mage::helper('eshopsync')->__('Customer Email'),
          'align'   =>'center',
          'index'   =>'email',
      ));

      $this->addColumn('customer_id',array(
        'header'  =>Mage::helper('eshopsync')->__('Magento Customer Id'),
        'align'   =>'center',
        'index'   =>'customer_id',
      ));

      $this->addColumn('magento_id',array(
        'header'  =>Mage::helper('eshopsync')->__('Magento Address Id'),
        'align'   =>'center',
        'index'   =>'magento_id',
      ));

      $this->addColumn('sforce_id', array(
          'header'    => Mage::helper('eshopsync')->__('Salesforce Contact Id'),
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

      $this->addExportType('*/*/exportCsv', Mage::helper('eshopsync')->__('CSV'));
      $this->addExportType('*/*/exportXml', Mage::helper('eshopsync')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('eshopsync_id');
        $this->getMassactionBlock()->setFormFieldName('eshopsync');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('eshopsync')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('eshopsync')->__('Are you sure?')
        ));

        return $this;
    }
}
