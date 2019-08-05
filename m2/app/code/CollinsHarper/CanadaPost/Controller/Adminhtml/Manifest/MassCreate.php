<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml\Manifest;


class MassCreate extends \CollinsHarper\CanadaPost\Controller\Adminhtml\AbstractController
{

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::manifests');
    }

    /**
     * manifet grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->chLogger->info(__METHOD__ . __LINE__);

        $shipmentIds = $this->getRequest()->getParam('manifest');

        $this->chLogger->info(__METHOD__ . __LINE__);

        if (!empty($shipmentIds) && is_array($shipmentIds)) {


            $this->chLogger->info(__METHOD__ . __LINE__);

            $manifest = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Manifest')
                ->setGroupId(time())
                ->setStatus('pending')
                ->setCreatedAt(date('Y-m-d H:i:s'))
                ->setUpdatedAt(date('Y-m-d H:i:s'))
                ->save();
            $this->chLogger->info(__METHOD__ . __LINE__);

            $added = 0;
            try {
                $added = $this->updateShipments($manifest->getId(), $manifest->getGroupId(), $shipmentIds, 'new');

            } catch (\Exception $e) {
                // it will cover below
                $this->chLogger->info(__METHOD__ . __LINE__  . " Exception " . $e->getMessage());
            }
            if ($added > 0) {

                $this->messageManager->addSuccess(__('%1 of %2 Shipment has been successfully created', $added, count($shipmentIds)));
            } else {

                $this->messageManager->addError(__('Failed to create manifest'));

                $manifest->delete();
            }

            $resultRedirect = $this->resultRedirectFactory->create();

          //  $this->_redirect('cpcanadapost/manifest/index');

        } else  {
            $this->messageManager->addSuccess(__('No shipments selected'));

        }

        return $resultRedirect->setPath('*/*/');

    }


}
