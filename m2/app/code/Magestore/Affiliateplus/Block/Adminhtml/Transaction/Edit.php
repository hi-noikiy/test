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
namespace Magestore\Affiliateplus\Block\Adminhtml\Transaction;

/**
 * Grid Grid
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magestore\Affiliateplus\Model\AccountFactory
     */
    protected $_accountFactory;

    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_helperConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magestore\Affiliateplus\Model\AccountFactory $accountFactory
     * @param \Magestore\Affiliateplus\Helper\Config $helperConfig
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magestore\Affiliateplus\Model\AccountFactory $accountFactory,
        \Magestore\Affiliateplus\Helper\Config $helperConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_accountFactory = $accountFactory;
        $this->_helperConfig = $helperConfig;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_objectId = 'transaction_id';
        $this->_blockGroup = 'Magestore_Affiliateplus';
        $this->_controller = 'adminhtml_transaction';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');

        $transaction = $this->getTransactionDataModel();
        if ($transaction && $transaction->getId()) {
            if (!$transaction->canRestore()) {
                $account = $this->_accountFactory->create()
                    ->setStoreId($transaction->getStoreId())
                    ->setBalanceIsGlobal(($this->_helperConfig->getAccountConfig('balance', $transaction->getStoreId()) == 'global'))
                    ->load($transaction->getAccountId());

                $totalCommission = $transaction->getCommission() + $transaction->getCommissionPlus()
                    + $transaction->getCommission() * $transaction->getPercentPlus() / 100 ;

                $this->_eventManager->dispatch('affiliateplus_adminhtml_prepare_commission', ['transaction' => $transaction]);

                if ($transaction->getRealTotalCommission()) {
                    $totalCommission = $transaction->getRealTotalCommission();
                }

                if ($account->getBalance() >= $totalCommission || $transaction->getStatus() != '1')  // Transaction Not Completed
                {
                    //Gin fix remove button Cancel
//                    if ($transaction->getStatus() != '3') {
//                        $this->buttonList->add('cancel', array(
//                            'label'     => __('Cancel'),
//                            'onclick'   => 'deleteConfirm(\''
//                                . __('This action cannot be restored. Are you sure?')
//                                . '\', \''
//                                . $this->getUrl('*/*/cancel', array('id' => $transaction->getId()))
//                                . '\')',
//                            'class'     => ''
//                        ), 0);
//                    }
                    //End
                    $transaction->setData('transaction_is_can_delete', true);
                }
            }
            // update form button
            $this->_eventManager->dispatch('affiliateplus_adminhtml_update_transaction_action',
                [
                    'transaction' => $transaction,
                    'form'        => $this
                ]
            );
        }

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('affiliateplus_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'affiliateplus_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'affiliateplus_content');
                }
            }
        ";
    }

     /**
     * get registry model.
     *
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    protected function getTransactionDataModel()
    {
        return $this->_coreRegistry->registry('transaction_data');
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->getTransactionDataModel()->getId()) {
            return __("View Transaction '%1'", $this->escapeHtml($this->getTransactionDataModel()->getAccountName()));
        } else {
            return __('View Transaction');
        }
    }
}
