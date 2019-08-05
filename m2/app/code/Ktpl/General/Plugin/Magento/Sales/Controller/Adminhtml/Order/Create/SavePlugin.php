<?php

namespace Ktpl\General\Plugin\Magento\Sales\Controller\Adminhtml\Order\Create;

class SavePlugin
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $quoteSession;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Session\Quote $quoteSession
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session\Quote $quoteSession
    ) {
        $this->storeManager=$storeManager;
        $this->quoteSession = $quoteSession;
    }

    /**
     * @param \Magento\Sales\Controller\Adminhtml\Order\Create\Save $subject
     */
    public function beforeExecute(
        \Magento\Sales\Controller\Adminhtml\Order\Create\Save $subject
    ) {
        $this->storeManager->setCurrentStore($this->quoteSession->getStore()); // solution work for de 
        return [];
    }
}