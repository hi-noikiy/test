<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Controller\Unsubscribe;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Aheadworks\StoreCredit\Api\Data\SummaryInterface;
use Aheadworks\StoreCredit\Model\Service\SummaryService;
use Aheadworks\StoreCredit\Model\KeyEncryptor;
use Aheadworks\StoreCredit\Model\Source\SubscribeStatus;
use Magento\Framework\DataObject;

/**
 * Class Index
 *
 * @package Aheadworks\StoreCredit\Controller\Unsubscribe
 */
class Index extends Action
{
    /**
     * @var SummaryService
     */
    private $summaryService;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var KeyEncryptor
     */
    private $keyEncryptor;

    /**
     * @param SummaryService $summaryService
     * @param Context $context
     * @param DataObject $dataObject
     * @param KeyEncryptor $keyEncryptor
     */
    public function __construct(
        Context $context,
        SummaryService $summaryService,
        DataObject $dataObject,
        KeyEncryptor $keyEncryptor
    ) {
        $this->summaryService = $summaryService;
        $this->dataObject = $dataObject;
        $this->keyEncryptor = $keyEncryptor;
        parent::__construct($context);
    }

    /**
     *  {@inheritDoc}
     */
    public function execute()
    {
        try {
            $unsubscribeData = $this->keyEncryptor->decrypt($this->getRequest()->getParam('key'));

            if (isset($unsubscribeData['customer_id'], $unsubscribeData['website_id'])) {
                $summaryData = $this->dataObject->setData(
                    [
                        SummaryInterface::CUSTOMER_ID => $unsubscribeData['customer_id'],
                        SummaryInterface::WEBSITE_ID => $unsubscribeData['website_id'],
                        SummaryInterface::BALANCE_UPDATE_NOTIFICATION_STATUS => SubscribeStatus::UNSUBSCRIBED
                    ]
                );
                $this->summaryService->updateCustomerSummary($summaryData);
                $this->messageManager->addSuccessMessage(__('Your Store Credit subscription settings were updated.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving your Store Credit subscription.')
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('/');
    }
}
