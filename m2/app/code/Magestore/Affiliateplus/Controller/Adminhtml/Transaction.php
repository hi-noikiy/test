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
namespace Magestore\Affiliateplus\Controller\Adminhtml;

/**
 * Action Account
 */
abstract class Transaction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magestore\Affiliateplus\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * Action constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Affiliateplus\Model\TransactionFactory $transactionFactory
    ) {
        parent::__construct($context);
        $this->_transactionFactory = $transactionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Affiliateplus::magestoreaffiliateplus');
    }
}
