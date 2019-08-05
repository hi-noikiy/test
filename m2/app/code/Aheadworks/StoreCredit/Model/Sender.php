<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model;

use Aheadworks\StoreCredit\Model\Config;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Aheadworks\StoreCredit\Model\KeyEncryptor;
use Aheadworks\StoreCredit\Model\Source\NotifiedStatus;

/**
 * Class Sender
 *
 * @package Aheadworks\StoreCredit\Model
 */
class Sender
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var KeyEncryptor
     */
    private $keyEncryptor;

    /**
     * @param Config $config
     * @param TransportBuilder $transportBuilder
     * @param StoreRepositoryInterface $storeRepository
     * @param KeyEncryptor $keyEncryptor
     */
    public function __construct(
        Config $config,
        TransportBuilder $transportBuilder,
        StoreRepositoryInterface $storeRepository,
        KeyEncryptor $keyEncryptor
    ) {
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
        $this->storeRepository = $storeRepository;
        $this->keyEncryptor = $keyEncryptor;
    }

    /**
     * Send email notification to recipient
     *
     * @param int $balanceUpdateAction
     * @param CustomerInterface $customer
     * @param string $comment
     * @param string $balance
     * @param int $storeId
     * @return int
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendUpdateBalanceNotification($balanceUpdateAction, $customer, $comment, $balance, $storeId)
    {
        $balanceUpdateActions = explode(',', $this->config->getBalanceUpdateActions());
        if (!in_array($balanceUpdateAction, $balanceUpdateActions)) {
            return NotifiedStatus::NO;
        }
        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        $store = $this->storeRepository->getById($storeId);
        $sender = $this->config->getEmailSender($store->getWebsiteId());
        $senderName = $this->config->getEmailSenderName($store->getWebsiteId());
        $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
        $key = $this->keyEncryptor->encrypt($customer->getEmail(), $customer->getId(), $store->getWebsiteId());

        $notifiedStatus = $this->send(
            $this->config->getBalanceUpdateEmailTemplate($store->getId()),
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $store->getId()
            ],
            $this->prepareTemplateVars(
                [
                    'store' => $store,
                    'sender_name' => $senderName,
                    'customer_name' => $customerName,
                    'comment' => $comment,
                    'balance' => $balance,
                    'unsubscribe_url' => $store->getBaseUrl() . 'aw_store_credit/unsubscribe/index/key/' . $key
                ]
            ),
            $sender,
            [$customerName => $customer->getEmail()]
        );
        return $notifiedStatus;
    }

    /**
     * Send email
     *
     * @param string $templateId
     * @param array $templateOptions
     * @param array $templateVars
     * @param string $from
     * @param array $to
     * @return int
     */
    private function send($templateId, array $templateOptions, array $templateVars, $from, array $to)
    {
        try {
            $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to);
            $this->transportBuilder->getTransport()->sendMessage();
        } catch (\Exception $e) {
            return NotifiedStatus::NO;
        }

        return NotifiedStatus::YES;
    }

    /**
     * Prepare template vars
     *
     * @param array $data
     * @return array
     */
    private function prepareTemplateVars($data)
    {
        $templateVars = [];

        /** @var $store \Magento\Store\Model\Store */
        $store = $data['store'];
        $templateVars['store'] = $store;
        $templateVars['store_name'] = $store->getFrontendName();
        $templateVars['store_url'] = $store->getBaseUrl();

        if (isset($data['sender_name'])) {
            $templateVars['sender_name'] = $data['sender_name'];
        }
        if (isset($data['customer_name'])) {
            $templateVars['customer_name'] = $data['customer_name'];
        }
        if (isset($data['comment'])) {
            $templateVars['comment'] = $data['comment'];
        }
        if (isset($data['balance'])) {
            $templateVars['balance'] = $data['balance'];
        }
        if (isset($data['unsubscribe_url'])) {
            $templateVars['unsubscribe_url'] = $data['unsubscribe_url'];
        }
        return $templateVars;
    }
}
