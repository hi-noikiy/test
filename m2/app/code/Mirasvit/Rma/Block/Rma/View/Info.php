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



namespace Mirasvit\Rma\Block\Rma\View;

class Info extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Helper\Order\Html $rmaOrderHtml,
        \Mirasvit\Rma\Helper\Order\Url $orderUrl,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->fieldManagement = $fieldManagement;
        $this->registry        = $registry;
        $this->rmaOrderHtml    = $rmaOrderHtml;
        $this->orderUrl        = $orderUrl;
        $this->rmaManagement   = $rmaManagement;
        $this->context         = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }


    /**
     * @param int|\Magento\Sales\Api\Data\OrderInterface  $orderId
     * @param bool $orderUrl
     * @return string
     */
    public function getOrderLabel($orderId, $orderUrl = false)
    {
        return $this->rmaOrderHtml->getOrderLabel($orderId, $orderUrl);
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        return $this->orderUrl->getUrl($orderId);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getStatusName(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaManagement->getStatus($rma)->getName();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @param bool $isEdit
     * @return \Mirasvit\Rma\Model\Field[]|
     */
    public function getCustomFields(\Mirasvit\Rma\Api\Data\RmaInterface $rma, $isEdit = false)
    {
        return $this->fieldManagement->getVisibleCustomerCollection($rma->getStatusId(), $isEdit);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getShippingAddressHtml($rma)
    {
        $items = [];
        $items[] = $this->escapeHtml($rma->getFirstname().' '.$rma->getLastname());
        if ($rma->getEmail()) {
            $items[] = $rma->getEmail();
        }
        if ($rma->getTelephone()) {
            $items[] = $rma->getTelephone();
        }
        if ($rma->getCompany()) {
            $items[] = $this->escapeHtml($rma->getCompany());
        }
        if ($rma->getStreet()) {
            $items[] = $this->escapeHtml($rma->getStreet());
        }
        if ($rma->getCity()) {
            $items[] = $this->escapeHtml($rma->getCity());
        }
        if ($rma->getRegion()) {
            $items[] = $this->escapeHtml($rma->getRegion());
        }
        if ($rma->getPostcode()) {
            $items[] = $this->escapeHtml($rma->getPostcode());
        }
        //@todo fix this
        //        if ($rma->getCountryId()) {
        //            $country = Mage::getModel('directory/country')->loadByCode($rma->getCountryId());
        //            $items[] = $country->getName();
        //        }

        return trim(implode('<br>', $items));
    }

    /**
     * @param \Magento\Framework\DataObject $rma
     * @param \Mirasvit\Rma\Model\Field     $field
     * @return bool|string
     */
    public function getRmaFieldValue(\Magento\Framework\DataObject $rma, \Mirasvit\Rma\Model\Field $field)
    {
        return $this->fieldManagement->getValue($rma, $field);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return bool|string
     */
    public function getReturnAddressHtml($rma)
    {
        return nl2br($this->rmaManagement->getReturnAddressHtml($rma));
    }

}
