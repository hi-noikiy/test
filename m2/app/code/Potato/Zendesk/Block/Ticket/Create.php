<?php
namespace Potato\Zendesk\Block\Ticket;

use Magento\Framework\View\Element\Template;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;
use Magento\Framework\Data\Form\FormKey;
use Potato\Zendesk\Api\Data\TicketInterface;

/**
 * Class Create
 */
class Create extends Template
{
    /** @var string  */
    protected $_template = 'ticket/create.phtml';

    /** @var TicketManagement  */
    protected $ticketManagement;

    /** @var FormKey  */
    protected $formKey;

    /**
     * Create constructor.
     * @param Template\Context $context
     * @param TicketManagement $ticketManagement
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        TicketManagement $ticketManagement,
        FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ticketManagement = $ticketManagement;
        $this->formKey = $formKey;
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        if ($ticket = $this->getTicket()) {
            $this->pageConfig->getTitle()->set(__('Ticket # %1 "%2"', $ticket->getId(), $ticket->getSubject()));
        } else {
            $this->pageConfig->getTitle()->set(__('Create new ticket'));
        }
        parent::_construct();
    }

    /**
     * @return null|TicketInterface
     */
    public function getTicket()
    {
        if (!$ticketId = $this->getRequest()->getParam('ticket_id', null)) {
            return null;
        }
        $store = $this->_storeManager->getStore();
        return $this->ticketManagement->getTicketById($ticketId, $store);
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
