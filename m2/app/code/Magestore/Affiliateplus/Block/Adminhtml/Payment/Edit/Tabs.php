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

namespace Magestore\Affiliateplus\Block\Adminhtml\Payment\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magestore\Affiliateplus\Model\PaymentFacetory
     */
    protected $_paymentFactory;

    /**
     * Tabs constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magestore\Affiliateplus\Model\PaymentFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magestore\Affiliateplus\Model\PaymentFactory $paymentFactory,
        array $data
    )
    {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->_paymentFactory = $paymentFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('payment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Payment Information'));
    }

    /**
     *
     * @return type
     */
    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => __('Withdrawal Information'),
            'title' => __('Withdrawal Information'),
            'content' => $this->getLayout()->createBlock('Magestore\Affiliateplus\Block\Adminhtml\Payment\Edit\Tab\Form')->toHtml(),
        ));

        if ($paymentId = $this->getRequest()->getParam('id')) {
            $payment = $this->_paymentFactory->create()->load($paymentId);
            if ($payment->getStatus() != 3) {
                $this->addTab('history_tab', array(
                    'label' => __('Status History'),
                    'title' => __('Status History'),
                    'content' => $this->getLayout()->createBlock('Magestore\Affiliateplus\Block\Adminhtml\Payment\Edit\Tab\History')->toHtml(),
                ));
            }
        }

        return parent::_beforeToHtml();
    }
}
