<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml;


use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


abstract class AbstractController extends \Magento\Backend\App\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \CollinsHarper\Core\Logger\Logger
     */
    protected $chLogger;

    /**
     * @var \CollinsHarper\CanadaPost\Helper\DataFactory
     */
    protected $objectFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \CollinsHarper\Core\Logger\Logger $chLogger
     * @param \CollinsHarper\CanadaPost\Helper\DataFactory $objectFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \CollinsHarper\Core\Logger\Logger $chLogger,
        \CollinsHarper\CanadaPost\Helper\DataFactory $objectFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->chLogger = $chLogger;
        $this->objectFactory = $objectFactory;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        $this->chLogger->info(__METHOD__ . __LINE__);
        return $this->_authorization->isAllowed('CollinsHarper_CanadaPost::shipments');
    }

    /**
     * 
     * @return CollinsHarper\Model\Manifest.php
     */
    protected function getLoadedManifest()
    {
        return $this->_coreRegistry->registry('manifest');
    }

    /**
     * 
     * @return bool
     */
    protected function loadManifest()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        $model = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Manifest');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('manifest', $model);
        return true;
    }

    // TODO move this to the shipment manager 
    // TODO Check the datatype of the following IDs
    /**
     * 
     * @param type $manifest_id
     * @param type $group_id
     * @param type $shipment_ids
     * @param string $action
     * @return int
     */
    protected function updateShipments($manifest_id, $group_id, $shipment_ids, $action)
    {

        $processed = 0;

        foreach ($shipment_ids as $shipmentId) {

            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);

            $cpShipment = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Shipment')->getShipmentById($shipmentId);
            $shipmentManagement = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Management\Shipment');

            if ($shipment->getId()) {

                switch ($action) {

                    case 'new':
                    case 'add':

                        if ($cpShipment->getManifestId() != $manifest_id) {

                            $createResult = $shipmentManagement->createCpShipment($group_id, $manifest_id, $shipmentId);

                            if (!$createResult) {

                                $this->messageManager->addError(__('Canada Post Shipment for shipment #%s has not been created', $shipment->getId()));

                                $this->messageManager->addError(__('Error: ') . (string)$createResult);

                                $this->chLogger->info(__METHOD__ . (string)$createResult);

                            } else {

                                $shipment->setManifestId( (string)$manifest_id)->save();

                                $processed++;

                            }

                        }

                        break;

                    case 'remove':
                        $this->chLogger->info(__METHOD__ . __LINE__ . " mid " . $manifest_id);
                        $this->chLogger->info(__METHOD__ . __LINE__ . " smid " . $cpShipment->getManifestId());
                        if ($cpShipment->getManifestId() == $manifest_id) {
                            $this->chLogger->info(__METHOD__ . __LINE__);
                            if ($shipmentManagement->removeCpShipment($cpShipment)) {
                                $this->chLogger->info(__METHOD__ . __LINE__);
                                $processed++;

                            }else {
                                $this->chLogger->info(__METHOD__ . __LINE__ . " error ");
                            }

                        }

                        break;

                }

            } else {

                $this->messageManager->addError(__('Shipment not found'));

            }

        }

        return $processed;

    }


}
