<?php

namespace CollinsHarper\Masterpass\Service\SoapApi;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class TransactionSale
 */
class TransactionSale extends \CollinsHarper\Masterpass\Service\Transaction implements ClientInterface
{
    
    /**
     *
     * @var \Magento\Checkout\Model\Session
     */
    private $session;
    
    public function __construct(
        \CollinsHarper\Masterpass\Gateway\Config\Config $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->session = $session;
        parent::__construct($scopeConfig, $logger, $urlBuilder);
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws LocalizedException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $this->_logger->info("transaction sale");
        return ['response' => 'ok'];
    }
}
