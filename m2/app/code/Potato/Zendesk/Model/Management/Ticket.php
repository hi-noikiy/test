<?php

namespace Potato\Zendesk\Model\Management;

use Potato\Zendesk\Api\TicketManagementInterface;
use Potato\Zendesk\Model\Authorization;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Zendesk\API\Exceptions\ApiResponseException;
use Potato\Zendesk\Api\Data\TicketInterface;
use Potato\Zendesk\Api\Data\TicketInterfaceFactory;
use Potato\Zendesk\Api\Data\MessageInterface;
use Potato\Zendesk\Api\Data\MessageInterfaceFactory;
use Potato\Zendesk\Api\Data\AttachmentInterface;
use Potato\Zendesk\Api\Data\AttachmentInterfaceFactory;
use Potato\Zendesk\Api\Data\UserInterface;
use Potato\Zendesk\Api\Data\UserInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\Store;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zendesk\API\HttpClient as ZendeskAPI;
use Zendesk\API\Exceptions\MissingParametersException;
use Zendesk\API\Exceptions\CustomException;
use Psr\Log\LoggerInterface;

/**
 * Class Customer
 */
class Ticket implements TicketManagementInterface
{
    /** @var Authorization  */
    protected $authorization;

    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var  TicketInterfaceFactory */
    protected $ticketFactory;

    /** @var MessageInterfaceFactory  */
    protected $messageFactory;

    /** @var AttachmentInterfaceFactory  */
    protected $attachmentFactory;

    /** @var UserInterfaceFactory  */
    protected $userFactory;

    /** @var CustomerSession  */
    protected $customerSession;

    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /** @var TicketInterface[]|array  */
    protected $ticketList = [];

    /** @var MessageInterface[]|array  */
    protected $messageList = [];
    
    /** @var LoggerInterface */
    protected $logger;

    /**
     * Ticket constructor.
     * @param Authorization $authorization
     * @param CustomerRepositoryInterface $customerRepository
     * @param TicketInterfaceFactory $ticketInterfaceFactory
     * @param MessageInterfaceFactory $messageInterfaceFactory
     * @param AttachmentInterfaceFactory $attachmentInterfaceFactory
     * @param UserInterfaceFactory $userInterfaceFactory
     * @param CustomerSession $customerSession
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Authorization $authorization,
        CustomerRepositoryInterface $customerRepository,
        TicketInterfaceFactory $ticketInterfaceFactory,
        MessageInterfaceFactory $messageInterfaceFactory,
        AttachmentInterfaceFactory $attachmentInterfaceFactory,
        UserInterfaceFactory $userInterfaceFactory,
        CustomerSession $customerSession,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->authorization = $authorization;
        $this->customerRepository = $customerRepository;
        $this->ticketFactory = $ticketInterfaceFactory;
        $this->messageFactory = $messageInterfaceFactory;
        $this->attachmentFactory = $attachmentInterfaceFactory;
        $this->userFactory = $userInterfaceFactory;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger; 
    }

    /**
     * @param int $customerId
     * @param null|integer|Store $store
     * @return array|TicketInterface[]
     */
    public function getTicketListByCustomerId($customerId, $store = null)
    {
        if ($this->ticketList) {
            return $this->ticketList;
        }
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            return $this->ticketList;
        }
        return $this->getTicketListByCustomerEmail($customer->getEmail(), $store);
    }

    /**
     * @param string $customerEmail
     * @param null|integer|Store $store
     * @return array|TicketInterface[]
     */
    public function getTicketListByCustomerEmail($customerEmail, $store = null)
    {
        $client = $this->authorization->connectToZendesk($store);
        if (null === $client) {
            return $this->ticketList;
        }
        try {
            // Search the current customer
            $params = ['query' => $customerEmail];
            $user = $this->searchUserByParams($params, $client);
            $params = ['sort_order' => 'desc', 'sort_by' => 'updated_at'];
            $tickets = $client->users($user->getId())->requests()->findAll($params);
            foreach ($tickets->requests as $ticket) {
                $ticketData = [
                    TicketInterface::ID => $ticket->id,
                    TicketInterface::URL => $this->getZendeskUrl('ticket', $ticket->id),
                    TicketInterface::STATUS => $ticket->status,
                    TicketInterface::SUBJECT => $ticket->subject,
                    TicketInterface::DESCRIPTION => $ticket->description,
                    TicketInterface::PRIORITY => $ticket->priority,
                    TicketInterface::CREATED_AT => $ticket->created_at,
                    TicketInterface::UPDATED_AT => $ticket->updated_at
                ];
                $this->ticketList[] = $this->ticketFactory->create(['data' => $ticketData]);
            }
        } catch (ApiResponseException $e) {
            $this->logger->critical($e->getMessage());
        }
        return $this->ticketList;
    }

    /**
     * @param int $ticketId
     * @param null|integer|Store $store
     * @return array|MessageInterface[]
     */
    public function getMessageListByTicketId($ticketId, $store)
    {
        if ($this->messageList) {
            return $this->messageList;
        }
        $client = $this->authorization->connectToZendesk($store);
        if (null === $client) {
            $this->logger->critical(__('Authorization to Zendesk failed'));
            return $this->messageList;
        }
        try {
            $params = ['sort_order' => 'desc'];
            $messages = $client->tickets($ticketId)->comments()->findAll($params);
            foreach ($messages->comments as $message) {
                if (!$message->public) {
                    continue;
                }
                $attachments = [];
                foreach ($message->attachments as $attachment) {
                    $attachmentData = [
                        AttachmentInterface::ID => $attachment->id,
                        AttachmentInterface::FILENAME => $attachment->file_name,
                        AttachmentInterface::CONTENT_URL => $attachment->content_url,
                        AttachmentInterface::SIZE => $attachment->size,
                        AttachmentInterface::TYPE => $attachment->content_type
                    ];
                    $attachments[] = $this->attachmentFactory->create(['data' => $attachmentData]);
                }
                $messageData = [
                    MessageInterface::ID => $message->id,
                    MessageInterface::AUTHOR_ID => $message->author_id,
                    MessageInterface::HTML_BODY => $message->html_body,
                    MessageInterface::CREATED_AT => $message->created_at,
                ];
                $message = $this->messageFactory->create(['data' => $messageData]);
                $message->setAttachments($attachments);
                $this->messageList[] = $message;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $this->messageList;
    }

    /**
     * @param int $ticketId
     * @param null|integer|Store $store
     * @return TicketInterface
     * @throws MissingParametersException
     */
    public function getTicketById($ticketId, $store)
    {
        $client = $this->authorization->connectToZendesk($store);
        if (null === $client) {
            return $this->ticketFactory->create();
        }
        try {
            $tickets  = $client->tickets()->find($ticketId);
            $ticketData = [
                TicketInterface::ID => $tickets->ticket->id,
                TicketInterface::URL => $this->getZendeskUrl('ticket', $tickets->ticket->id),
                TicketInterface::STATUS => $tickets->ticket->status,
                TicketInterface::SUBJECT => $tickets->ticket->subject,
                TicketInterface::DESCRIPTION => $tickets->ticket->description,
                TicketInterface::PRIORITY => $tickets->ticket->priority,
                TicketInterface::CREATED_AT => $tickets->ticket->created_at,
                TicketInterface::UPDATED_AT => $tickets->ticket->updated_at
            ];
            return $this->ticketFactory->create(['data' => $ticketData]);
        } catch (ApiResponseException $e) {
            $this->logger->critical($e->getMessage());
        }
        return $this->ticketFactory->create();
    }

    /**
     * @param int $authorId
     * @param null|integer|Store $store
     * @return UserInterface
     * @throws MissingParametersException
     */
    public function getUserByAuthorId($authorId, $store)
    {
        $client = $this->authorization->connectToZendesk($store);
        if (null === $client) {
            return $this->userFactory->create();
        }
        try {
            $users  = $client->users()->find($authorId);
            $userData = [
                UserInterface::ID => $users->user->id,
                UserInterface::NAME => $users->user->name,
                UserInterface::ROLE => $users->user->role,
            ];
            if (null !== $users->user->photo) {
                $userData[UserInterface::PHOTO] = $users->user->photo->content_url;
            }
            return $this->userFactory->create(['data' => $userData]);
        } catch (ApiResponseException $e) {
            $this->logger->critical($e->getMessage());
        }
        return $this->userFactory->create();
    }

    /**
     * @param array $ticketData
     * @param int|Store|null $store
     * @param array $attachments
     * @return null|\stdClass
     * @throws \Exception
     */
    public function updateTicket($ticketData, $store, $attachments = [])
    {
        $client = $this->authorization->connectToZendesk($store);
        if (null === $client) {
            throw new \Exception(__('Authorization to Zendesk failed'));
        }
        $attachmentList = $this->prepareAttachments($client, $attachments);
        if (null === $customer = $this->getCustomer()) {
            throw new \Exception(__('Customer not found'));
        }
        $params = ['query' => $customer->getEmail()];
        $user = $this->searchUserByParams($params, $client);

        $params = [
            'status' => 'open',
            'comment'  => [
                'html_body' => $ticketData['comment'],
                'author_id' => $user->getId()
            ]
        ];
        if (!empty($attachmentList)) {
            $params['comment']['uploads'] = $attachmentList;
        }
        return $client->tickets()->update($ticketData['ticket_id'], $params);
    }

    /**
     * @param array $ticketData
     * @param int|Store|null $store
     * @param array $attachments
     * @return null|\stdClass
     * @throws \Exception
     */
    public function createTicket($ticketData, $store, $attachments = [])
    {
        $client = $this->authorization->connectToZendesk($store);
        if (null === $client) {
            throw new \Exception(__('Authorization to Zendesk failed'));
        }
        $attachmentList = $this->prepareAttachments($client, $attachments);
        if (null !== $customer = $this->getCustomer()) {
            $email = $customer->getEmail();
            $name = $customer->getFirstname() . ' ' . $customer->getLastname();
        } elseif (array_key_exists('order_id', $ticketData)) {
            $order = $this->orderRepository->get($ticketData['order_id']);
            $email = $order->getCustomerEmail();
            $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        } elseif (array_key_exists('id', $ticketData)) {
            $customer = $this->customerRepository->getById($ticketData['id']);
            $email = $customer->getEmail();
            $name = $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            throw new \Exception(__('Customer not found'));
        }
        $params = [
            'comment'  => [
                'html_body' => $ticketData['comment']
            ],
            'subject'  => $ticketData['subject'],
            'requester' => [
                'name' => $name,
                'email' => $email,
            ]
        ];
        if (!empty($attachmentList)) {
            $params['comment']['uploads'] = $attachmentList;
        }
        return $client->tickets()->create($params);
    }

    /**
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        try {
            $customer = $this->customerSession->getCustomerData();
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $customer;
    }

    /**
     * @param array $params
     * @param ZendeskAPI $client
     * @return UserInterface
     */
    private function searchUserByParams($params, $client)
    {
        $search = $client->users()->search($params);
        foreach ($search->users as $user) {
            $userData = [
                UserInterface::ID => $user->id,
                UserInterface::NAME => $user->name,
                UserInterface::ROLE => $user->role,
            ];
            if (null !== $user->photo) {
                $userData[UserInterface::PHOTO] = $user->photo->content_url;
            }
            return $this->userFactory->create(['data' => $userData]);
        }
        return $this->userFactory->create();
    }

    /**
     * @param string $object
     * @param null|int $id
     * @param string $format
     * @return string
     */
    public function getZendeskUrl($object = '', $id = null, $format = 'old')
    {
        return $this->authorization->getZendeskUrl($object, $id, $format);
    }

    /**
     * @param ZendeskAPI $client
     * @param array $attachments
     * @return array
     */
    private function prepareAttachments($client, $attachments = [])
    {
        $attachmentList = [];
        if (empty($attachments) || !array_key_exists('error', $attachments)) {
            return $attachmentList;
        }
        foreach ($attachments["error"] as $key => $error) {
            if ($error !== UPLOAD_ERR_OK) {
                continue;
            }
            $uploadedFile = $client->attachments()->upload([
                'file' => $attachments['tmp_name'][$key],
                'type' => $attachments['type'][$key],
                'name' => $attachments['name'][$key]
            ]);
            $attachmentList[] = $uploadedFile->upload->token;

        }
        return $attachmentList;
    }
}
