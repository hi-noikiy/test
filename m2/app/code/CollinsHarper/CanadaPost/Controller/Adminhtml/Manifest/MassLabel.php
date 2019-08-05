<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml\Manifest;


class MassLabel extends \CollinsHarper\CanadaPost\Controller\Adminhtml\AbstractController
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
        $manifestId = $this->getRequest()->getParam('manifest_id');

        $this->chLogger->info(__METHOD__ . __LINE__);

        if ($manifestId && !empty($shipmentIds) && is_array($shipmentIds)) {


            $this->chLogger->info(__METHOD__ . __LINE__);
            $added = 0;

            $manifest = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Manifest')->load($manifestId);
            if($manifest->getStatus() == 'pending') {
                try {
                    $added = $this->updateShipments($manifest->getId(), $manifest->getGroupId(), $shipmentIds, 'add');
                } catch (\Exception $e) {
                    // it will cover below
                }

            } else {
                $this->messageManager->addError(__('Manifest is not pending'));
            }

            $this->chLogger->info(__METHOD__ . __LINE__);


            if ($added > 0) {

                $this->messageManager->addSuccess(__('%1 of %2 Shipment has been successfully added', $added, count($shipmentIds)));
            } else {

                $this->messageManager->addError(__('Failed to add shipments to manifest'));

            }

            $resultRedirect = $this->resultRedirectFactory->create();

          //  $this->_redirect('cpcanadapost/manifest/index');

        } else  {
            $this->messageManager->addSuccess(__('No shipments selected'));

        }

        return $resultRedirect->setPath('*/*/');

    }


}
