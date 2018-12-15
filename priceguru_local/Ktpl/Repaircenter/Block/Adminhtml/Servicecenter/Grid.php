<?php

class Ktpl_Repaircenter_Block_Adminhtml_Servicecenter_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('servicecenterGrid');
      $this->setDefaultSort('service_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('repaircenter/servicecenter')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('service_id', array(
          'header'    => Mage::helper('repaircenter')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'service_id',
      ));

      $this->addColumn('service_name', array(
          'header'    => Mage::helper('repaircenter')->__('Title'),
          'align'     =>'left',
          'index'     => 'service_name',
      ));

      $this->addColumn('service_address', array(
          'header'    => Mage::helper('repaircenter')->__('Address'),
          'align'     => 'left',
          'index'     => 'service_address'
      ));
	  
      $this->addColumn('service_latitude', array(
            'header'    => Mage::helper('repaircenter')->__('Latitude'),
            'width'     => '150px',
            'index'     => 'service_latitude',
      ));

      $this->addColumn('service_longitude', array(
            'header'    => Mage::helper('repaircenter')->__('Longitude'),
            'width'     => '150px',
            'index'     => 'service_longitude',
      ));
	  

      $this->addColumn('service_status', array(
          'header'    => Mage::helper('repaircenter')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'service_status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
      $this->addColumn('action',
          array(
              'header'    =>  Mage::helper('repaircenter')->__('Action'),
              'width'     => '100',
              'type'      => 'action',
              'getter'    => 'getId',
              'actions'   => array(
                  array(
                      'caption'   => Mage::helper('repaircenter')->__('Edit'),
                      'url'       => array('base'=> '*/*/edit'),
                      'field'     => 'id'
                  )
              ),
              'filter'    => false,
              'sortable'  => false,
              'index'     => 'stores',
              'is_system' => true,
      ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('repaircenter')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('repaircenter')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('service_id');
        $this->getMassactionBlock()->setFormFieldName('servicecenter');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('repaircenter')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('repaircenter')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('customreport/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('service_status', array(
             'label'=> Mage::helper('repaircenter')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'service_status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('repaircenter')->__('Status'),
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