<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('mageworkshop_importexportreview/history_collection');
        $collection->getSelect()->joinInner(
            array('sync' => $collection->getTable('mageworkshop_importexportreview/sync')),
            'main_table.sync_id = sync.id',
            array('store_name' => 'sync.store_name')
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('store_name', array(
            'header' => $this->_getHelper()->__('Store Name'),
            'type' => 'text',
            'index' => 'store_name',
        ));

        $this->addColumn('type', array(
            'header' => $this->_getHelper()->__('Sync Type'),
            'type' => 'text',
            'index' => 'type',
            'renderer' => 'MageWorkshop_ImportExportReview_Block_Adminhtml_History_Grid_Renderer_Type'
        ));
        $this->addColumn('reviews_count', array(
            'header' => $this->_getHelper()->__('Reviews Processed'),
            'type' => 'text',
            'index' => 'reviews_count',
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return MageWorkshop_ImportExportReview_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('mageworkshop_importexportreview');
    }
}