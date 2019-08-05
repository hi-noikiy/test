<?php
/**
 * Copyright © 2016 CollinsHarper. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CollinsHarper\Moneris\Block\Adminhtml;

class RecurringListing extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml_post';
        $this->_blockGroup = 'CollinsHarper_Moneris';
        $this->_headerText = __('Recurring Payment');
        $this->setData(self::PARAM_BUTTON_NEW, false);
        parent::_construct();
    }
}
