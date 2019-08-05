<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Controller\Adminhtml\Account;

class Save extends \Amasty\Affiliate\Controller\Adminhtml\Account
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                /** @var \Amasty\Affiliate\Model\Account $model */
                $model = $this->accountFactory->create();
                $data = $this->getRequest()->getPostValue();
                $model->setData($data);
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model = $this->accountRepository->get($id);
                }

                $this->accountRepository->save($model);
                $this->messageManager->addSuccessMessage(__('The account is saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_affiliate/account/edit', ['id' => $model->getAccountId()]);
                    return;
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('amasty_affiliate/account/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_affiliate/account/new');
                }
                return;
            }

        }
        $this->_redirect('amasty_affiliate/account/index');
    }
}
