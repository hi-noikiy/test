<?php
class Amasty_Email_Block_Adminhtml_Sales_Order_View_Tab extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amemailGrid');
        $this->setUseAjax(true);
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
    }

    protected function _prepareCollection()
    {
        $logs = Mage::getResourceModel('amemail/log_collection')
            ->addFieldToFilter('order_id', Mage::registry('current_order_id'));
        $this->setCollection($logs);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('amemail');

        $this->addColumn('sent_date', array(
            'header'    => $hlp->__('Sent Date'),
            'index'     => 'sent_date',
            'gmtoffset' => true,
            'type'      => 'datetime',
            //'width'     => '200px',
        ));
        
        $this->addColumn('code', array(
            'header'    => $hlp->__('Text'),
            'index'     => 'code',
//            'type'      => 'options', 
//            'options'   => $hlp->getAvailableTemplates(),
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('amemail/adminhtml_index/order', array('_current'=>true));
    }

}