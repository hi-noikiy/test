<?php


class Justselling_Configurator_Block_Adminhtml_Jobprocessor_Job_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->setId('job_grid');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
	}


	/**
	 * @see Mage_Adminhtml_Block_Widget_Grid::_prepareCollection()
	 */
	protected function _prepareCollection()	{
	    $session = Mage::getSingleton('admin/session');
	    /* @var $user Mage_Admin_Model_User */
	    $user = $session->getUser();
	    $userName = $user->getUsername();
	    $job = Mage::getModel('configurator/jobprocessor_job');
		$collection = $job->getCollection();
  		//$collection->addFieldToFilter('created_by', $userName);
		$this->setCollection($collection);
		parent::_prepareCollection();
		return $this;
	}

	protected function _prepareColumns() {
		$this->addColumn('id', array(
			'header' => Mage::helper('configurator/job')->__('ID'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'id',
		));
		$this->addColumn('name', array(
			'header' => Mage::helper('configurator/job')->__('Name'),
			'align' => 'left',
			'index' => 'name',
		));
		$jobModel = Mage::getModel('configurator/jobprocessor_job');
		$this->addColumn('status', array(
			'header' => Mage::helper('configurator/job')->__('Status'),
			'align' => 'left',
			'index' => 'status',
		    'type'  => 'options',
            'options' => $jobModel->getStatusOptionHash(true)
		));
		$this->addColumn('processes_active', array(
		        'header' => Mage::helper('configurator/job')->__('Prozesse Aktiv'),
		        'align' => 'right',
		        'width' => '70px',
		        'index' => 'processes_active',
		));
		$this->addColumn('count_total', array(
		        'header' => Mage::helper('configurator/job')->__('Total'),
		        'align' => 'right',
		        'width' => '70px',
		        'index' => 'count_total',
		));
		$this->addColumn('count_done', array(
		        'header' => Mage::helper('configurator/job')->__('Processed'),
		        'align' => 'right',
		        'width' => '70px',
		        'index' => 'count_done',
		));
		$this->addColumn('count_problems', array(
		        'header' => Mage::helper('configurator/job')->__('Failures'),
		        'align' => 'right',
		        'width' => '70px',
		        'index' => 'count_problems',
		));
		$this->addColumn('created_by', array(
		        'header'    => Mage::helper('configurator/job')->__('Created by'),
		        'align'     => 'left',
		        'index'     => 'created_by',
		        'width'     => '140',
		));
		$this->addColumn('created_at', array(
			'header'    => Mage::helper('configurator/job')->__('Created at'),
			'align'     => 'left',
			'index'     => 'created_at',
			'width'     => '140',
            'type'      => 'datetime'
		));
		$this->addColumn('started_at', array(
			'header'    => Mage::helper('configurator/job')->__('Started at'),
			'align'     => 'left',
			'index'     => 'started_at',
			'width'     => '140',
            'type'      => 'datetime'
		));
		$this->addColumn('finished_at', array(
			'header'    => Mage::helper('configurator/job')->__('Finished at'),
			'align'     => 'left',
			'index'     => 'finished_at',
			'width'     => '140',
            'type'      => 'datetime'
		));
		parent::_prepareColumns();
		return $this;
	}

}