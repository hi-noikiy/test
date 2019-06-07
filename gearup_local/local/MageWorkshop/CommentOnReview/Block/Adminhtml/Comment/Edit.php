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

class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'mageworkshop_commentonreview';
        $this->_controller = 'adminhtml_comment';

        /**@var MageWorkshop_CommentOnReview_Helper_Data $commentOnReviewHelper*/
        $commentOnReviewHelper = Mage::helper('mageworkshop_commentonreview');

        $this->_updateButton('save', 'label', $commentOnReviewHelper->__('Save Comment'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', $commentOnReviewHelper->__('Delete Comment'));

        if ($this->getRequest()->getParam($this->_objectId)) {
            $commentData = Mage::getModel('review/review')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('comment_data', $commentData);
        }
    }

    public function getHeaderText()
    {
        $helper = Mage::helper('mageworkshop_commentonreview');

        if (Mage::registry('comment_data') && Mage::registry('comment_data')->getId()) {
            return $helper->__("Edit Comment '%s'", $this->escapeHtml(Mage::registry('comment_data')->getTitle()));
        }
    }
}
