<?php
/**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Gearup_Competera_Block_Adminhtml_Competera_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('competeraHistoryGrid');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('competera/competerahistory')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn('history_id', array(
            'header'    => Mage::helper('competera')->__('ID'),
            'width'     => '50px',
            'index'     => 'history_id',
            'type'  => 'number',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('competera')->__('Title'),
            'align'     => 'left',
            'index'     => 'title',
        ));

        $this->addColumn('creation_time',
            array(
                'header'=> Mage::helper('competera')->__('Created At'),
                'width' => '150px',
                'index' => 'creation_time',
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('competera')->__('Action'),
                'width'     => '120px',
                'align'     => 'center',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('competera')->__('View Change Log'),
                        'url'     => array(
                            'base'=>'*/competera_pricechange',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));
       
    }

    public function getRowUrl($row)
    {
        return false;
    }
}
