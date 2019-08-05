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

class AddFieldTransactionForm extends AbtractObserver implements ObserverInterface
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
        $data = $observer->getEvent()->getForm()->getTransationData();
        $fieldset = $observer->getEvent()->getFieldset();
        $block = $observer->getEvent()->getBlock();

        $transactionPrograms = $this->_programTransactionCollectionFactory->create()
            ->addFieldToFilter('transaction_id', $data['transaction_id']);

        $text = array();
        if ($transactionPrograms->getSize()){
            foreach ($transactionPrograms as $transactionProgram) {
                if ($transactionProgram->getProgramId()) {
                    $url = $block->getUrl('affiliateplusadmin/program/edit', array(
                        '_current' => true,
                        'program_id' => $transactionProgram->getProgramId(),
                        'store' => $data['store_id'],
                    ));
                    $title = __('View Program Detail');
                    $label = $transactionProgram->getProgramName();
                } else {
                    $url = $block->getUrl('adminhtml/system_config/edit/section/affiliateplus');
                    $title = __('View Program Configuration Detail');
                    $label = __('Affiliate Program');
                }
                $text[] = '<a href="' . $url . '" title="' . $title . '">' . $label . '</a>';
            }
        }else {
            $url = $block->getUrl('adminhtml/system_config/edit/section/affiliateplus');
            $title = __('View Program Configuration Detail');
            $label = __('Affiliate Program');
            $text[] = '<a href="' . $url . '" title="' . $title . '">' . $label . '</a>';
        }

        $fieldset->addField(
            'program_ids',
            'note',
            [
                'name' => 'program_ids',
                'label' => __('Program(s)'),
                'text' => implode(' , ', $text)
            ]
        );

        return $this;
    }
}