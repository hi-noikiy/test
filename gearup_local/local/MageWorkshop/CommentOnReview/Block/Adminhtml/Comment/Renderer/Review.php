<?php

/**
 * Class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Renderer_Review
 */
class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Renderer_Review extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Add Column to All Comment grid with link on Main Review. Frontend
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $reviewUrl = Mage::helper('mageworkshop_commentonreview/admin')->getLinkOnReview($row);
        return sprintf('<a href="%s" onclick="this.target=\'blank\'">%s</a>', $reviewUrl, $row->getTitle());
    }
}
