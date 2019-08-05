<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml\Manifest;


class PrintShipments extends \CollinsHarper\CanadaPost\Controller\Adminhtml\AbstractController
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

        $ids = $this->getRequest()->getParam('manifest');

        if (!empty($ids) && is_array($ids)) {

            $pdf = new \Zend_Pdf;

            $print = false;

            foreach ($ids as $shipmentId) {


                $label_data = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Link')->getLabelDataByMageShipmentId($shipmentId);

                if (!empty($label_data['url']) && !empty($label_data['media_type'])) {

                    ob_start();

                    $this->_objectManager->create('CollinsHarper\CanadaPost\Helper\Rest\Pdf')->load($label_data['url'], $label_data['media_type'], '', 0);

                    $pdfString = ob_get_contents();

                    ob_end_clean();

                    if (!empty($pdfString)) {

                        try {

                            $this->_objectManager->create('CollinsHarper\CanadaPost\Helper\Rest\Pdf')->addPage($pdf, $pdfString);

                            $print = true;

                        } catch (Exception $e) {
                            $this->messageManager->addError(__('Error: $1', $e->getMessage()));

                        }

                    }

                }

                $invoice_data = $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Link')->getLabelDataByMageShipmentId($shipmentId, 'commercial');

                if (!empty($invoice_data['url']) && !empty($invoice_data['media_type'])) {

                    ob_start();

                    $this->_objectManager->create('CollinsHarper\CanadaPost\Helper\Rest\Pdf')->load($invoice_data['url'], $invoice_data['media_type'], '', 0);

                    $pdfString = ob_get_contents();

                    ob_end_clean();

                    if (!empty($pdfString)) {

                        $this->_objectManager->create('CollinsHarper\CanadaPost\Helper\Rest\Pdf')->addPage($pdf, $pdfString);

                        $print = true;

                    }

                }

            }

            if ($print) {

                header('content-type: application/pdf');

                header('Content-Disposition: attachment; filename="labels-'.date('Y-m-d--H-i-s').'.pdf"');

                echo $pdf->render();

            } else {

                $this->messageManager->addError(__('Labels can not be retrieved'));

                if (!empty($_SERVER['HTTP_REFERER'])) {

                    $this->_redirect($_SERVER['HTTP_REFERER']);

                } else {

                    return $resultRedirect->setPath('*/*/');


                }

            }

        } else {

            return $resultRedirect->setPath('*/*/');

        }

    }


}
