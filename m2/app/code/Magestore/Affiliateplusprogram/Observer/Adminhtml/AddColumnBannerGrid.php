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

class AddColumnBannerGrid extends AbtractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $grid = $observer->getEvent()->getGrid();
        $grid->addColumn('program_id', array(
            'header' => __('Program Name'),
            'index' => 'program_id',
            'align' => 'left',
            'type' => 'options',
            'options' => $this->_helper->getProgramOptions()
        ));
        return $this;
    }
}