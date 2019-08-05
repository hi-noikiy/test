<?php

namespace Potato\Zendesk\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Potato\Zendesk\Model\Authorization;
use Magento\Store\Model\StoreManagerInterface;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 */
class Save extends Action
{
    /** @var Authorization  */
    protected $authorization;

    /** @var  StoreManagerInterface */
    protected $storeManager;
    
    /** @var TicketManagement  */
    protected $ticketManagement;

    /** @var  LoggerInterface */
    protected $logger;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param Authorization $authorization
     * @param StoreManagerInterface $storeManager
     * @param TicketManagement $ticketManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        Authorization $authorization,
        StoreManagerInterface $storeManager,
        TicketManagement $ticketManagement,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->authorization = $authorization;
        $this->storeManager = $storeManager;
        $this->ticketManagement = $ticketManagement;
        $this->logger = $logger;
    }
     /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $attachments = [];
        if (!empty($_FILES) && array_key_exists('file', $_FILES)) {
            $attachments = $_FILES["file"];
        }
        if (!$postData) {
            $this->messageManager->addErrorMessage(__('Data not found.'));
            return $resultRedirect->setPath('*/*/history');
        }
        $store = $this->storeManager->getStore();
        
        try {
            $this->ticketManagement->createTicket($postData, $store, $attachments);
            $this->messageManager->addSuccessMessage(__('The ticket was been successfully created.'));
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the ticket.'));
        }
        return $resultRedirect->setRefererUrl();
    }
}
