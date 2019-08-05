<?php

namespace Krish\CriticReview\Block\Adminhtml\Review\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('review_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Review Information'));
    }
}
