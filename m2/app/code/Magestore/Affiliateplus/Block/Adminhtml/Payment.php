<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Adminhtml;

/**
 * Grid Container Account
 */
class Payment extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_payment';
        $this->_blockGroup = 'Magestore_Affiliateplus';
        $this->_headerText = __('Account Payment Grid');
        $this->_addButtonLabel = __('Add New Withdrawal');

        parent::_construct();
        $this->removeButton('add');
        $this->addButton('add_withdrawal', [
            'label'     => __('Add Withdrawal'),
            'onclick'   => 'setLocation(\''.$this->getUrl('affiliateplusadmin/payment/selectAccount').'\')',
            'class'     => 'add primary'
        ], 0, 100, 'header', 'header');
    }
}
