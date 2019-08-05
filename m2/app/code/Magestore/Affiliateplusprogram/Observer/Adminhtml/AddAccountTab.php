<?php
/**
 * Magestore
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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplusprogram\Observer\AbtractObserver;

class AddAccountTab extends AbtractObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $block = $observer->getEvent()->getForm();

        $block->addTab('program_section', array(
            'label' => __('Programs'),
            'title' => __('Programs'),
            'url' => $block->getUrl('affiliateplusadmin/program/program', array(
                '_current' => true,
                'account_id' => $block->getRequest()->getParam('account_id'),
                'store' => $block->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
            'after' => 'form_section',
        ));
        return $this;
    }
}