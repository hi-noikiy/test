<?php
namespace Magenest\Salesforce\Controller\Adminhtml\Sync;

use Magenest\Salesforce\Model\Sync;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Class Opportunity
 * @package Magenest\Salesforce\Controller\Adminhtml\Sync
 */
class Opportunity extends Action
{
    /**
     * @var Sync\Opportunity
     */
    protected $_opportunity;

    /**
     * Opportunity constructor.
     * @param Context $context
     * @param Sync\Opportunity $opportunity
     */
    public function __construct(
        Context $context,
        Sync\Opportunity $opportunity
    ) {
        $this->_opportunity = $opportunity;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $opportunityIncrementId = $this->getRequest()->getParam('id');
            if ($opportunityIncrementId) {
                $this->_opportunity->sync($opportunityIncrementId);
                $this->messageManager->addSuccess(
                    __('Opportunity sync process complete, check out Reports for result.')
                );
            } else {
                $this->messageManager->addNotice(
                    __('No opportunity has been selected')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something happen during syncing process. Detail: ' . $e->getMessage())
            );
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Salesforce::config_salesforce');
    }
}
