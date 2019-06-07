<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_CommentOnReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var Mage_Review_Helper_Data $reviewHelper
     */
    protected $reviewHelper;

    /**
     * @var MageWorkshop_CommentOnReview_Helper_Data $commentOnReviewHelper
     */
    protected $commentOnReviewHelper;

    public function __construct()
    {
        parent::__construct();
        $this->setId('commentGrid');
        $this->setDefaultSort('created_at');
        $this->setReviewHelper(Mage::helper('review'));
        $this->setCommentOnReviewHelper(Mage::helper('mageworkshop_commentonreview'));
    }

    protected function _prepareCollection()
    {
        /** @var Mage_Review_Model_Review $review */
        $review = Mage::getModel('review/review');
        $reviewCollection = $review->getCollection();
        $reviewCollection->addFieldToFilter('main_table.entity_id', $review->getEntityIdByCode('review'));

        /** @var Mage_Core_Model_Resource $coreResource */
        $coreResource   = Mage::getSingleton('core/resource');

        $reviewTable       = $coreResource->getTableName('review/review');
        $productTable      = $coreResource->getTableName('catalog/product');
        $reviewDetailTable = $coreResource->getTableName('review/review_detail');
        $complaintTable    = $coreResource->getTableName('detailedreview/review_customer_complaint');

        if ($reviewCollection->getSize()) {
            $reviewCollection->getSelect()
                ->join(
                    array('parent_review' => $reviewTable),
                    'main_table.entity_pk_value = parent_review.review_id',
                    array('product_id' => 'parent_review.entity_pk_value')
                )
                ->join(
                    array('product' => $productTable),
                    'product.entity_id = parent_review.entity_pk_value',
                    array('sku' => 'product.sku')
                )
                ->join(
                    array('rdetail' => $reviewDetailTable),
                    'rdetail.review_id = main_table.review_id',
                    array('store_id' => 'rdetail.store_id')
                )
                ->joinLeft(
                    array('complaint' => $complaintTable),
                    'complaint.review_id = main_table.review_id',
                    array('complaint_count' => 'COUNT(complaint.review_id)')
                )
                ->group('main_table.review_id');

            $reviewCollection->addStoreData();
        }

        if (Mage::registry('usePendingFilter') === true) {
            $reviewCollection->addStatusFilter($review->getPendingStatus());
        }

        $this->setCollection($reviewCollection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('review_id', array(
            'header'        => $this->reviewHelper->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'main_table.review_id',
            'index'         => 'review_id',
        ));

        $this->addColumn('created_at', array(
            'header'        => $this->reviewHelper->__('Created On'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'filter_index'  => 'main_table.created_at',
            'index'         => 'created_at',
        ));

        /** @var MageWorkshop_CommentOnReview_Helper_Data $commentOnReviewHelper */
        $commentOnReviewHelper = Mage::helper('mageworkshop_commentonreview');
        
        if( !Mage::registry('usePendingFilter') ) {
            $this->addColumn('status', array(
                'header'        => $this->reviewHelper->__('Status'),
                'align'         => 'left',
                'type'          => 'options',
                'options'       => $commentOnReviewHelper->getReviewStatuses(),
                'width'         => '100px',
                'filter_index'  => 'main_table.status_id',
                'index'         => 'status_id',
            ));
        }

        $this->addColumn('title', array(
            'header'        => $this->reviewHelper->__('Title'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'detail.title',
            'index'         => 'title',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('nickname', array(
            'header'        => $this->reviewHelper->__('Nickname'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'detail.nickname',
            'index'         => 'nickname',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('detail', array(
            'header'        => $this->reviewHelper->__('Comment'),
            'align'         => 'left',
            'index'         => 'detail',
            'filter_index'  => 'detail.detail',
            'renderer'      => 'MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Renderer_CommentDetail',
            'type'          => 'text',
            'truncate'      => 50,
            'nl2br'         => true,
            'escape'        => true,
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('visible_in', array(
                'header'        => $this->reviewHelper->__('Visible In'),
                'index'         => 'store_id',
                'filter_index'  => 'rdetail.store_id',
                'type'          => 'store',
                'store_view'    => true,
            ));
        }

        $this->addColumn('type', array(
            'header'    => $this->reviewHelper->__('Type'),
            'type'      => 'select',
            'index'     => 'type',
            'filter'    => 'adminhtml/review_grid_filter_type',
            'renderer'  => 'adminhtml/review_grid_renderer_type',
             'sortable' => false
        ));

        $this->addColumn('sku', array(
            'header'    => $this->reviewHelper->__('Product SKU'),
            'align'     => 'right',
            'type'      => 'text',
            'width'     => '50px',
            'index'     => 'sku',
            'escape'    => true
        ));

        $this->addColumn('main_review_id', array(
            'header'        => $this->commentOnReviewHelper->__('Main Review Id'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'main_table.entity_pk_value',
            'index'         => 'entity_pk_value',
        ));

        $this->addColumn('complaint_count', array(
            'header'    => $this->commentOnReviewHelper->__('Number of reports'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'complaint_count',
            'filter'    => false,
            'escape'    => true
        ));

        $this->addColumn('main_review_link',
            array(
                'header'    => $this->commentOnReviewHelper->__('Main Review Link'),
                'index'     => 'title',
                'renderer'  => 'MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Renderer_Review',
                'filter'    => false,
                'sortable'  => true
            )
        );

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('adminhtml')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getReviewId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('adminhtml')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/mageworkshop_commentonreview_comment/edit',
                            'params'=> array(
                                'productId' => $this->getProductId(),
                                'customerId' => $this->getCustomerId(),
                                'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null
                            )
                         ),
                         'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('review_id');
        $this->setMassactionIdFilter('rt.review_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('reviews');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> $this->reviewHelper->__('Delete'),
            'url'  => $this->getUrl(
                '*/*/massDelete',
                array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')
            ),
            'confirm' => $this->reviewHelper->__('Are you sure?')
        ));
    
        /** @var MageWorkshop_CommentOnReview_Helper_Data $commentOnReviewHelper */
        $commentOnReviewHelper = Mage::helper('mageworkshop_commentonreview');

        $statuses = $commentOnReviewHelper->getReviewStatusesOptionArray();
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('update_status', array(
            'label'         => $this->reviewHelper->__('Update Status'),
            'url'           => $this->getUrl(
                '*/*/massUpdateStatus',
                array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')
            ),
            'additional'    => array(
                'status'    => array(
                    'name'      => 'status',
                    'type'      => 'select',
                    'class'     => 'required-entry',
                    'label'     => $this->reviewHelper->__('Status'),
                    'values'    => $statuses
                )
            )
        ));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/mageworkshop_commentonreview_comment/edit', array(
            'id' => $row->getReviewId(),
            'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null,
        ));
    }

    public function getGridUrl()
    {
        if ($this->getProductId() || $this->getCustomerId()) {
            return $this->getUrl(
                '*/mageworkshop_commentonreview_comment/' . (Mage::registry('usePendingFilter') ? 'pending' : ''),
                array(
                    'productId' => $this->getProductId(),
                    'customerId' => $this->getCustomerId(),
                )
            );
        } else {
            return $this->getCurrentUrl();
        }
    }

    /**
     * @return Mage_Review_Helper_Data
     */
    public function getReviewHelper()
    {
        return $this->reviewHelper;
    }

    /**
     * @param Mage_Review_Helper_Data $reviewHelper
     */
    public function setReviewHelper($reviewHelper)
    {
        $this->reviewHelper = $reviewHelper;
    }

    /**
     * @return MageWorkshop_CommentOnReview_Helper_Data
     */
    public function getCommentOnReviewHelper()
    {
        return $this->commentOnReviewHelper;
    }

    /**
     * @param MageWorkshop_CommentOnReview_Helper_Data $commentOnReviewHelper
     */
    public function setCommentOnReviewHelper($commentOnReviewHelper)
    {
        $this->commentOnReviewHelper = $commentOnReviewHelper;
    }

}
