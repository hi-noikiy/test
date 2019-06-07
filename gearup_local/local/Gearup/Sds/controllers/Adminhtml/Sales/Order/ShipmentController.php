<?php
if (file_exists(Mage::getBaseDir().'/app/code/local/Fenomics/OrderEmail/controllers/Adminhtml/Sales/Order/ShipmentController.php')) {
    include_once("Fenomics/OrderEmail/controllers/Adminhtml/Sales/Order/ShipmentController.php");
    class Gearup_Sds_Adminhtml_Sales_Order_Shipment extends Fenomics_OrderEmail_Adminhtml_Sales_Order_ShipmentController { }
} else {
    include_once("Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php");
    class Gearup_Sds_Adminhtml_Sales_Order_Shipment extends Mage_Adminhtml_Sales_Order_ShipmentController { }
}
class Gearup_Sds_Adminhtml_Sales_Order_ShipmentController extends Gearup_Sds_Adminhtml_Sales_Order_Shipment
{

    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');

        if ($data['ffdx_type']) {
            Mage::getSingleton('core/session')->unsShippingffdx();
            Mage::getSingleton('core/session')->setShippingffdx($data['ffdx_type']);
        }
        Mage::register('posta_shipping_data', $this->getRequest()->getQuery());
            
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = $this->__('The shipment has been created.');
            //$labelCreatedMessage    = $this->__('The shipping label has been created. <a href="%s">print</a>',Mage::helper('adminhtml')->getUrl('*/sales_order_shipment/printLabel', array('shipment_id' => $shipment->getId())));
            $labelCreatedMessage    = $this->__('The shipping label has been created.');
            if ($isNeedCreateLabel) {
                Mage::getSingleton('adminhtml/session')->setAutoprint($shipment->getId());
            }
            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);

            $order = $shipment->getOrder();
            if ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery') {
                $history = Mage::getModel('sales/order_status_history')
                    ->setStatus('complete')
                    ->setComment('')
                    ->setEntityName(Mage_Sales_Model_Order::HISTORY_ENTITY_NAME)
                    ->setIsCustomerNotified(false);
                $order->addStatusHistory($history);
                $order->setStatus('complete', true);
                $order->save();

            }
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }

    public function createLabelAction()
    {
        $response = new Varien_Object();
        try {
            if ($this->getRequest()->getParam('ffdx_type')) {
                Mage::getSingleton('core/session')->unsShippingffdx();
                Mage::getSingleton('core/session')->setShippingffdx($this->getRequest()->getParam('ffdx_type'));
            }
            
            Mage::register('posta_shipping_data', $this->getRequest()->getQuery());                 
            
            if (!empty($data['comment_text'])) {
                Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
            }
            $shipment = $this->_initShipment();
            if ($this->_createShippingLabel($shipment)) {
                $shipment->save();
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The shipping label has been created.'));
                $response->setOk(true);
            }
        } catch (Mage_Core_Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $response->setError(true);
            $response->setMessage(Mage::helper('sales')->__('An error occurred while creating shipping label.'));
        }

        $this->getResponse()->setBody($response->toJson());
    }
}