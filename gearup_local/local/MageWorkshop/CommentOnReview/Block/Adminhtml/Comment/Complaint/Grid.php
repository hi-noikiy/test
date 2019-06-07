<?php
/**
 * Adminhtml complaint grid block
 *
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_CommentOnReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Complaint_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('complaintGrid');
        $this->setDefaultSort('entity_id');
    }

    protected function _prepareCollection()
    {
        $resource  = Mage::getSingleton('core/resource');
        $reviewDetailedTable = $resource->getTableName('review/review_detail');
        $complaintTable      = $resource->getTableName('mageworkshop_commentonreview/complaint_type');

        $collection = Mage::getModel('mageworkshop_commentonreview/reviewCustomerComplaint')->getCollection();

        if ($collection->getSize()) {
            $collection->getSelect()
                ->join(
                    array('review_detailed' => $reviewDetailedTable),
                    'review_detailed.review_id = main_table.review_id',
                    array('detail' => 'detail')
                )
                ->join(
                    array('review_complaint' => $complaintTable ),
                    'review_complaint.entity_id = main_table.complaint_id',
                    array('title' => 'review_complaint.title')
                );
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('mageworkshop_commentonreview');

        $this->addColumn('review_id', array(
            'header'        => Mage::helper('review')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'main_table.review_id',
            'index'         => 'review_id',
        ));

        $this->addColumn('detail', array(
            'header'        => $helper->__('Review Text'),
            'align'         => 'left',
            'filter_index'  => 'detail',
            'index'         => 'detail',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('title', array(
            'header'        => $helper->__('Complaint Text'),
            'align'         => 'left',
            'filter_index'  => 'review_complaint.title',
            'index'         => 'title',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        return parent::_prepareColumns();
    }
}
