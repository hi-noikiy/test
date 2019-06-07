<?php

/**
 * Class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Renderer_Review
 */
class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Renderer_CommentDetail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        return Mage::helper('mageworkshop_commentonreview/admin')->getCommentDetail($row);
    }
}
