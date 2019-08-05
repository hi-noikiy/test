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

namespace Paysafe\Paysafe\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuoteSubmitSuccess implements ObserverInterface
{
    /**
     * Backend Auth session model identifier
     *
     * @var \Magento\Backend\Model\Auth\Session $authSession
     */
    protected $_authSession;

    /**
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(\Magento\Backend\Model\Auth\Session $authSession)
    {
        $this->_authSession = $authSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isAdminLoggedIn = $this->_authSession->isLoggedIn();
        if (!$isAdminLoggedIn) {
            $quote = $observer->getEvent()->getQuote();
            $paymentMethod = $quote->getPayment()->getMethod();
            if (strpos($paymentMethod, 'paysafe') !== false) {
                $quote->setIsActive(true);
                $quote->setReservedOrderId(null);
            }
        }
    }
}
