<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_DetailedReview_Adminhtml_Mageworkshop_Detailedreview_Comment_ComplaintController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Reviews and Ratings'))
            ->_title($this->__('Customer Comments'));

        $this->_title($this->__('List of Complaints'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('CommentComplaintGrid');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('detailedreview/adminhtml_comment_complaint_main'));

        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/commentcomplaint');
    }
}
