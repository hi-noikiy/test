<?php

class Ktpl_Customreport_Block_Adminhtml_Wholesaler_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('wholesalerGrid');
      $this->setDefaultSort('wholesaler_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('customreport/wholesaler')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('wholesaler_id', array(
          'header'    => Mage::helper('customreport')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'wholesaler_id',
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('customreport')->__('Title'),
          'align'     =>'left',
          'index'     => 'name',
      ));

      $this->addColumn('address', array(
          'header'    => Mage::helper('customreport')->__('Address'),
          'align'     => 'left',
          'index'     => 'address'
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('helloworld')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('customreport')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
      $this->addColumn('action',
          array(
              'header'    =>  Mage::helper('customreport')->__('Action'),
              'width'     => '100',
              'type'      => 'action',
              'getter'    => 'getId',
              'actions'   => array(
                  array(
                      'caption'   => Mage::helper('customreport')->__('Edit'),
                      'url'       => array('base'=> '*/*/edit'),
                      'field'     => 'id'
                  )
              ),
              'filter'    => false,
              'sortable'  => false,
              'index'     => 'stores',
              'is_system' => true,
      ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('customreport')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('customreport')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('wholesaler_id');
        $this->getMassactionBlock()->setFormFieldName('wholesaler');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('customreport')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('customreport')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('customreport/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('customreport')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('customreport')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}