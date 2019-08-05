<?php

namespace CollinsHarper\Moneris\Block\Frontend;

use Magento\Framework\Session\SessionManagerInterface;

abstract class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * @var \CollinsHarper\Moneris\Helper\Data
     */
    public $chHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $checkoutSession;

    /**
     * @var \CollinsHarper\Core\Model\ObjectFactory
     */
    public $objectFactory;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    public $quote;

    /**
     * AbstractBlock constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\BlockRepository $blockRepository
     * @param SessionManagerInterface $customerSession
     * @param SessionManagerInterface $checkoutSession
     * @param \CollinsHarper\Moneris\Helper\Data $chHelper
     * @param \CollinsHarper\Core\Model\ObjectFactory $objectFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\BlockRepository $blockRepository,
        \CollinsHarper\Moneris\Helper\Data $chHelper,
        \CollinsHarper\Core\Model\ObjectFactory $objectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_request = $context->getRequest();
        $this->_layout = $context->getLayout();
        $this->_eventManager = $context->getEventManager();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->blockRepository = $blockRepository;
        $this->chHelper = $chHelper;
        $this->objectFactory = $objectFactory;
        $this->customerSession = $context->getSession();
        $this->checkoutSession = $context->getSession();
        $this->messageManager = $messageManager;
        $this->_cache = $context->getCache();

        parent::__construct($context, $data);
    }
    
    public function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->getCheckoutSession()->getQuote();
        }
        
        return $this->quote;
    }

    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    public function getCheckoutHelper()
    {
        return $this->chHelper;
    }
}
