<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml complaint grid block
 *
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Comment_Complaint_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $complaintTable      = $resource->getTableName('detailedreview/complaint_type');

        $collection = Mage::getModel('detailedreview/reviewCustomerComplaint')->getCollection();

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
        $helper = Mage::helper('detailedreview');

        $this->addColumn('review_id',
            array(
                'header'    => Mage::helper('adminhtml')->__('ID'),
                'align'         => 'right',
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getReviewId',
                'actions'   => array(
                    array(
                        'caption' => '',
                        'url'     => array(
                            'base'   => '*/catalog_product_review/edit',
                            'params' => array('complaintsList' => $this->getId())
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter_index'  => 'main_table.review_id',
                'index'         => 'review_id'
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
