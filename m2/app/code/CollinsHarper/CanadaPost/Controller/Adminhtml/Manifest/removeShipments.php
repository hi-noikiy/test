<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml\Manifest;


class removeShipments extends \CollinsHarper\CanadaPost\Controller\Adminhtml\AbstractController
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

        $resultRedirect = $this->resultRedirectFactory->create();
        $this->chLogger->info(__METHOD__ . __LINE__);
        $shipmentIds = $this->getRequest()->getParam('manifest');

        $manifestId = $this->getRequest()->getParam('manifest_id');
        $manifest = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Manifest')->load($manifestId);

        $forceDelete = (int)$this->getRequest()->getParam('_delete') == 1;
        $this->chLogger->info(__METHOD__ . __LINE__);
        $shipmentsDeleted = 0;
        $this->chLogger->info(__METHOD__ . __LINE__);
        if (!empty($shipmentIds) && is_array($shipmentIds) && !empty($manifestId)) {
            $this->chLogger->info(__METHOD__ . __LINE__);
            $removed = 0;

            $this->chLogger->info(__METHOD__ . __LINE__);
            if($manifest->getId()) {
                $this->chLogger->info(__METHOD__ . __LINE__);
                $removed = $this->updateShipments($manifest->getId(), $manifest->getGroupId(), $shipmentIds, 'remove');

            } else {
                $this->chLogger->info(__METHOD__ . __LINE__);
                $this->messageManager->addError(__('Manifest not found.'));
            }

            if($forceDelete) {
                $this->chLogger->info(__METHOD__ . __LINE__);
                foreach($shipmentIds  as $shipmentId) {
                    $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
                    $this->chLogger->info(__METHOD__ . __LINE__);
                    $shipment->delete();
                    $this->chLogger->info(__METHOD__ . __LINE__);
                    $shipmentsDeleted++;
                }

                if($shipmentsDeleted) {
                    $this->chLogger->info(__METHOD__ . __LINE__);
                    $this->messageManager->addSuccess(__('%1 shipment%2 deleted.', $shipmentsDeleted, ($shipmentsDeleted > 1 ? 's' : '')));
                }

            } else if($removed > 0)  {
                $this->chLogger->info(__METHOD__ . __LINE__);
                $this->messageManager->addSuccess(__('%1 shipment%2 removed.', $removed, ($removed > 1 ? 's' : '')));

            } else {
                $this->chLogger->info(__METHOD__ . __LINE__);
                $this->messageManager->addError(__('No shipments were removed.'));
            }
            $this->chLogger->info(__METHOD__ . __LINE__);
            $shipmentsInManifest = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Shipment')->getCollection()
                ->addFieldToFilter('manifest_id', $manifestId)
                ->getSize();

            if(empty($shipmentsInManifest) || $shipmentsInManifest == 0) {
                $this->chLogger->info(__METHOD__ . __LINE__);
                $manifest->delete();
                return $resultRedirect->setPath('*/*/');
            }  else {
                $this->chLogger->info(__METHOD__ . __LINE__);
                return $resultRedirect->setPath('*/*/view', array('manifest_id' => $manifestId));
            }
        }
        $this->chLogger->info(__METHOD__ . __LINE__);
        return $resultRedirect->setPath('*/*/');

    }

}
