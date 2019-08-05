<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Model\Session;

class EditPost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    private $account;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;
    /**
     * @var \Amasty\Affiliate\Model\Url
     */
    private $url;

    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        Session $customerSession,
        \Amasty\Affiliate\Model\Account $account,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\Url $url
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $context->getMessageManager();
        $this->account = $account;
        $this->session = $customerSession;
        $this->accountRepository = $accountRepository;
        $this->url = $url;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();
            $data = $this->prepareData($data);
            if (isset($data['accepted_terms_conditions']) && $data['accepted_terms_conditions'] != 'on') {
                $this->messageManager->addErrorMessage(__('You should accept "Accept Terms and Conditions'));
                return $resultRedirect->setPath('amasty_affiliate/account/setting');
            }

            /** @var \Amasty\Affiliate\Model\Account $account */
            $account = $this->accountRepository->getCurrentAccount();
            if ($account->getAccountId() == null) {
                $this->account->createAccount($this->session->getCustomerId(), $data);
            } else {
                $account->addData($data);
                $this->accountRepository->save($account);
            }
        }

        $this->messageManager->addSuccessMessage(__('Affiliate Account has successfully saved'));

        return $resultRedirect->setPath($this->url->getPath('account/setting'));
    }

    protected function prepareData($data)
    {
        if (isset($data['receive_notifications']) && $data['receive_notifications'] == 'on') {
            $data['receive_notifications'] = 1;
        } else {
            $data['receive_notifications'] = 0;
        }

        return $data;
    }
}
