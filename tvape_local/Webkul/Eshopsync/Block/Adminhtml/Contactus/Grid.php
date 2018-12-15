<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Contactus_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('contactusGrid');
      $this->setDefaultSort('entity_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('eshopsync/contactus')->getCollection();
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

      $this->addColumn('name',array(
          'header'  =>Mage::helper('eshopsync')->__('Name'),
          'align'   =>'center',
          'index'   =>'name',
      ));


      $this->addColumn('email',array(
          'header'  =>Mage::helper('eshopsync')->__('Contact Email'),
          'align'   =>'center',
          'index'   =>'email',
      ));

      $this->addColumn('phone',array(
        'header'  =>Mage::helper('eshopsync')->__('Telephone'),
        'align'   =>'center',
        'index'   =>'phone',
      ));

      $this->addColumn('sforce_id', array(
          'header'    => Mage::helper('eshopsync')->__('Salesforce lead Id'),
          'align'     =>'center',
          'index'     => 'sforce_id',
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

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
}
