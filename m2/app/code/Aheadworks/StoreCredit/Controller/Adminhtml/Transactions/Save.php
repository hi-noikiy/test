<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Controller\Adminhtml\Transactions;

use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Aheadworks\StoreCredit\Controller\Adminhtml\Transactions\Save
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_StoreCredit::aw_store_credit_transaction_save';

    /**
     * @var PostDataProcessor
     */
    private $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditService;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        CustomerStoreCreditManagementInterface $customerStoreCreditService
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->customerStoreCreditService = $customerStoreCreditService;
    }

    /**
     *  {@inheritDoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
                $this->dataPersistor->set('transaction', $data);
                $data = $this->dataProcessor->filter($data);
                $this->processSave($data);
                $this->dataPersistor->clear('transaction');
                $this->messageManager->addSuccessMessage(__('You saved the transactions.'));
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the transaction.')
                );
            }
            return $resultRedirect->setPath('*/*/new');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $data
     * @throws LocalizedException
     * @return void
     */
    private function processSave(array $data)
    {
        $customerSelection = $this->dataProcessor->customerSelectionFilter($data);

        if (!empty($customerSelection)) {
            foreach ($customerSelection as $transactionData) {
                $this->customerStoreCreditService->resetCustomer();
                $this->customerStoreCreditService->saveAdminTransaction($transactionData);
            }
        } else {
            throw new LocalizedException(
                __('Please select customers or confirm that they belong to the website of the current transaction')
            );
        }
    }
}
