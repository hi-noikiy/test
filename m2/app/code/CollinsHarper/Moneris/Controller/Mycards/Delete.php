<?php
/**
 * Copyright © 2016 CollinsHarper. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CollinsHarper\Moneris\Controller\Mycards;

use CollinsHarper\Moneris\Controller\AbstractMycards;

class Delete extends AbstractMycards
{
    protected function _execute()
    {
        $id = $this->getTokenId();
        $this->vaultSaveService->deleteVault($id);
        $this->messageManager->addSuccessMessage(__('Your Credit Card has been deleted successfully.'));
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
