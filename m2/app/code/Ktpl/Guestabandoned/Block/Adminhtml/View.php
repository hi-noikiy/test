<?php

namespace Ktpl\Guestabandoned\Block\Adminhtml;


use Magento\Customer\Model\Address\Config as AddressConfig;

class View extends \Magento\Framework\View\Element\Template {

    //@param \Magento\Reports\Model\ResourceModel\Quote\Item\CollectionFactory 
    protected $quoteCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, AddressConfig $addressConfig, \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory, \Magento\Framework\Pricing\Helper\Data $priceFormatter, array $data = []) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->priceCurrency = $priceFormatter;
        $this->addressConfig = $addressConfig;

        parent::__construct($context, $data);
    }

    public function getHeaderText($quote) {
        $id = $this->getRequest()->getParam('entity_id');
        return __('ID # %1 | %2', $id, $this->formateCreatedDate($quote));
    }

    public function formateCreatedDate($quote) {
        return $this->formatDate(
                        $this->_localeDate->date(new \DateTime($quote->getCreatedAt())), \IntlDateFormatter::MEDIUM, true
        );
    }

    public function getQuote() {
        $id = $this->getRequest()->getParam('entity_id');
        $quote = $this->quoteCollectionFactory->create()->addFieldToFilter('entity_id', $id);

        return $quote->getFirstItem();
    }

    /**
     * Format price
     *
     * @param float $value
     * @return string
     */
    public function formatPrice($value) {
        return $this->priceCurrency->currency($value, true, false);
    }

    /**
     * Format address in a specific way
     *
     * @param Address $address
     * @param string $type
     * @return string|null
     */
    public function format($address, $type,$storeId) {
       
        $this->addressConfig->setStore($storeId);
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }

        return $formatType->getRenderer()->renderArray($address);
    }

}
