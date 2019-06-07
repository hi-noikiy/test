<?php
/**
 * Review edit form
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

class MageWorkshop_DetailedReview_Block_Adminhtml_Review_Edit extends Mage_Adminhtml_Block_Review_Edit
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'review';

        if( $this->getRequest()->getParam('complaintsList', false) ) {
            $this->_updateButton('back', 'label', Mage::helper('detailedreview')->__('Back to List of Complaints on Comments'));
            $this->_updateButton(
                'back',
                'onclick',
                'setLocation(\''
                    . $this->getUrl(
                        '*/mageworkshop_detailedreview_comment_complaint/index'
                    )
                    .'\')'
            );
        }
    }

}
