<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysafe
 * @copyright   Copyright (c) 2017 Paysafe
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Paysafe\Paysafe\Controller\Payment;

/**
 * redirect to payment form template
 *
 */
class PaRes extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    /**
     * loads layout
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $blockPaysafe = $resultPage->getLayout()->getBlock('paysafePaymentPaRes');
        $blockPaysafe->setPaReq(base64_decode($this->getRequest()->getParam('paReq')));
        $blockPaysafe->setAcsURL(base64_decode($this->getRequest()->getParam('acsURL')));
        $paResResponseUrl = $this->_url->getUrl(
            'paysafe/payment/response',
            [
                '_secure' => true
            ]
        );
        $blockPaysafe->setPaResResponseUrl($paResResponseUrl);

        return $resultPage;
    }
}
