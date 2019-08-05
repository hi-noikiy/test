<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Service\Rma;
use Mirasvit\Rma\Api\Data\RmaInterface;

/**
 * We put here only methods directly connected with RMA properties
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RmaManagement implements \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface $statusRepository,
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement,
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->statusRepository      = $statusRepository;
        $this->rmaConfig             = $rmaConfig;
        $this->localeDate            = $localeDate;
        $this->countryFactory        = $countryFactory;
        $this->orderRepository       = $orderRepository;
        $this->attachmentManagement  = $attachmentManagement;
        $this->customerFactory       = $customerFactory;
        $this->userFactory           = $userFactory;
        $this->storeRepository       = $storeRepository;
        $this->escaper               = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(RmaInterface $rma)
    {
        return $this->statusRepository->get($rma->getStatusId());
    }


    /**
     * {@inheritdoc}
     */
    public function getOrder(RmaInterface $rma)
    {
        return $this->orderRepository->get($rma->getOrderId());
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomer(RmaInterface $rma)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerFactory->create()->load($rma->getCustomerId());
        if ($customer->getId()) {
            return $customer;
        }

        $order = $this->getOrder($rma);
        $customer->setEmail($this->escaper->escapeHtml($order->getCustomerEmail()));
        if ($address = $order->getBillingAddress()) {
            $customer->setFirstname($this->escaper->escapeHtml($address->getFirstname()));
            $customer->setLastname($this->escaper->escapeHtml($address->getLastname()));
        } elseif ($address = $order->getShippingAddress()) {
            $customer->setFirstname($this->escaper->escapeHtml($address->getFirstname()));
            $customer->setLastname($this->escaper->escapeHtml($address->getLastname()));
        }

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(RmaInterface $rma)
    {
        return $this->userFactory->create()->load($rma->getUserId());
    }

    /**
     * {@inheritdoc}
     */
    public function getStore(RmaInterface $rma)
    {
        return $this->storeRepository->getById($rma->getStoreId());
    }

    /**
     * {@inheritdoc}
     */
    public function getTicket(RmaInterface $rma)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig */
        $helpdeskConfig = $objectManager->create('\Mirasvit\Rma\Api\Config\HelpdeskConfigInterface');
        if (!$rma->getTicketId() || !$helpdeskConfig->isHelpdeskActive()) {
            return false;
        }
        /** @var \Mirasvit\Helpdesk\Model\Ticket $ticket */
        $ticket = $objectManager->create('\Mirasvit\Helpdesk\Model\TicketFactory')->create();
        $ticket->getResource()->load($ticket, $rma->getTicketId());

        return $ticket;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullName(RmaInterface $rma)
    {
        return $this->escaper->escapeHtml($rma->getFirstname() .' '.$rma->getLastname());
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnLabel($rma)
    {
        return $this->attachmentManagement->getAttachment(
            \Mirasvit\Rma\Api\Config\AttachmentConfigInterface::ATTACHMENT_ITEM_RETURN_LABEL, $rma->getId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddressHtml(RmaInterface $rma)
    {
        $items = [];
        $items[] = $this->escaper->escapeHtml($rma->getFirstname().' '.$rma->getLastname());
        if ($rma->getEmail()) {
            $items[] = $this->escaper->escapeHtml($rma->getEmail());
        }
        if ($rma->getTelephone()) {
            $items[] = $this->escaper->escapeHtml($rma->getTelephone());
        }
        if ($rma->getCompany()) {
            $items[] = $this->escaper->escapeHtml($rma->getCompany());
        }
        if ($rma->getStreet()) {
            $items[] = $this->escaper->escapeHtml($rma->getStreet());
        }
        if ($rma->getCity()) {
            $items[] = $this->escaper->escapeHtml($rma->getCity());
        }
        if ($rma->getRegion()) {
            $items[] = $this->escaper->escapeHtml($rma->getRegion());
        }
        if ($rma->getCountryId()) {
            $country = $this->countryFactory->create()->loadByCode($rma->getCountryId());
            $items[] = $country->getName();
        }

        return implode('<br>', $items);
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnAddressHtml(RmaInterface $rma)
    {
        $address = $rma->getReturnAddress();
        if (!$address) {
            $address = $this->rmaConfig->getReturnAddress($rma->getStoreId());
        }

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(RmaInterface $rma)
    {
        return $rma->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAtFormated(RmaInterface $rma)
    {
        $format = \IntlDateFormatter::MEDIUM;
        $date = new \DateTime($rma->getCreatedAt());

        return $this->localeDate->formatDateTime($date, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAtFormated(RmaInterface $rma)
    {
        $format = \IntlDateFormatter::MEDIUM;
        $date = new \DateTime($rma->getUpdatedAt());

        return $this->localeDate->formatDateTime($date, $format);
    }
}

