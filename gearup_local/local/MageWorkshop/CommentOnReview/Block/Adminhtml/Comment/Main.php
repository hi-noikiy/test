<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Main extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_removeButton('add');

        $this->_controller = 'adminhtml_comment';
        $this->_blockGroup = 'mageworkshop_commentonreview';

        if( Mage::registry('usePendingFilter') === true ) {
            $this->_headerText = Mage::helper('mageworkshop_commentonreview')->__('Pending Comments');
        } else {
            $this->_headerText = Mage::helper('mageworkshop_commentonreview')->__('All Comments');
        }
    }
}
