<?php
/**
 * Adminhtml complaint main block
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

class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Complaint_Main extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_removeButton('add');
        $this->_controller = 'adminhtml_comment_complaint';
        $this->_blockGroup = 'mageworkshop_commentonreview';

        $this->_headerText = Mage::helper('mageworkshop_commentonreview')->__('List of Complaints on Comments');
    }
}
