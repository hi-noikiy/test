<?php
class EM_Mobapp_Block_Adminhtml_Mobapp_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('mobappGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('mobapp/store')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('mobapp_id', array(
          'header'    => Mage::helper('mobapp')->__('Apps ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('mobapp')->__('Apps name'),
          'align'     =>'left',
          'index'     => 'name',
      ));

	  $this->addColumn('update_time', array(
          'header'    => Mage::helper('mobapp')->__('Last Modified'),
          'align'     =>'left',
          'index'     => 'update_time',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('mobapp')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Active',
              0 => 'Inactive',
          ),
      ));

      return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}