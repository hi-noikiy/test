<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Controller\Adminhtml\Program;

class Save extends \Amasty\Affiliate\Controller\Adminhtml\Program
{
    public function execute()
    {
        /** @var \Amasty\Affiliate\Model\Program $model */
        $model = $this->programFactory->create();

        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model = $this->programRepository->get($id);
                }

                $model->loadPost($data);
                $this->programRepository->save($model);
                $this->messageManager->addSuccessMessage(__('The program is saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_affiliate/program/edit', ['id' => $model->getProgramId()]);
                    return;
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('amasty_affiliate/program/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_affiliate/program/new');
                }
                return;
            }

        }
        $this->_redirect('amasty_affiliate/program/index');
    }
}
