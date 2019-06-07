<?php

/**
 * FFDX ShippingBox pages controller
 *
 * @category   FFDX
 * @package    FFDX_ShippingBox
 */
class FFDX_ShippingBox_Adminhtml_TrackingController extends Mage_Adminhtml_Controller_Action
{
    protected $_objectId = 'tracking_id';
    protected $_blockGroup = 'ffdxshippingbox';
    protected $_controller = 'adminhtml_tracking';

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/ffdxshippingbox');
    }
    /**
     * Init actions
     *
     * @return FFDX_ShippingBox_Adminhtml_TrackingController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('ffdxshippingbox/tracking')
            ->_addBreadcrumb(Mage::helper('ffdxshippingbox')->__('Shipping Control'), Mage::helper('ffdxshippingbox')->__('Shipping History Control'))
            ->_addBreadcrumb(Mage::helper('ffdxshippingbox')->__('Shipping'), Mage::helper('ffdxshippingbox')->__('Shipping History'))
        ;
        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__('FFDX'))->_title($this->__('ShippingBox'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * View Tracking action
     */
    public function historyAction()
    {
        $tracking = Mage::getModel('ffdxshippingbox/tracking')->load($this->getRequest()->getParam('tracking_id'));

        Mage::register('current_tracking', $tracking);
        $this->_title($this->__('FFDX'))->_title($this->__('ShippingBox'))->_title('Nr: ' . $tracking->getTrackingNumber());

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Load tracks from table sales_shipment_tracks
     */

    public function loadAction()
    {
        $shipmentCollection = Mage::getModel('sales/order_shipment_track')
            ->getCollection()->addFieldToFilter(
                array('track_number', 'track_number'),
                array(
                    array('gt' => 100000000000),
                    array('like' => 'Delivered%')
                )
            );

        foreach ($shipmentCollection as $shipping) {
            $trackingCollection = Mage::getModel('ffdxshippingbox/tracking')->getUncheckedTracks();

            if (is_null($trackingCollection)) {
                Mage::getModel('ffdxshippingbox/tracking')
                    ->setOrderId($shipping->getOrderId())
                    ->setShipmentId($shipping->getParentId())
                    ->setTrackingNumber($shipping->getTrackNumber())
                    ->save();
            } else {
                $existedShipping = $trackingCollection->getLastItem();
                if (!$existedShipping->getTrackingId()) {
                    Mage::getModel('ffdxshippingbox/tracking')
                        ->setOrderId($shipping->getOrderId())
                        ->setShipmentId($shipping->getParentId())
                        ->setTrackingNumber($shipping->getTrackNumber())
                        ->save();
                }
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ffdxshippingbox')->__('Loading of all tracks has been done.'));
        $this->_redirect('*/*/index');
    }

    /**
     * Refresh
     */
    public function refreshAction()
    {
        try {
            Mage::getSingleton('ffdxshippingbox/observer')->checkAll();
            Mage::getModel('core/config')->saveConfig('ffdxshippingbox/lastRefresh/date', Mage::helper('ffdxshippingbox')->getNow());
            Mage::app()->cleanCache();
        } catch (Exception $e) {
            Mage::log('Refreshing error: ' . $e->getMessage(), null,  'shippingbox_refereshing.log');
        }

        Mage::dispatchEvent('shippingbox_refresh_after');

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ffdxshippingbox')->__('Refreshing has been done.'));
        $this->_redirect('*/*/index');
    }

    /**
     * Check only one track given in form Adminhtml_Tracking_Edit_Form
     */
    public function check_oneAction()
    {
        $this->_title($this->__('FFDX'))->_title($this->__('Check Tracking'));

        $tracking = Mage::getModel('ffdxshippingbox/tracking');

        Mage::register('tracking_to_check_in_form', $tracking);

        if ($data = $this->getRequest()->getPost()) {

            $trackingNumber = $data['tracking_number'];

            Mage::register('tracking_number', $trackingNumber);
        }
        $this->_initAction()
            ->renderLayout();
    }

    public function cleanAction()
    {
        try {
            Mage::getModel('ffdxshippingbox/tracking')->cleanAll();
        } catch (Exception $e) {
            Mage::log('Refreshing error: ' . $e->getMessage(), null,  'shippingbox_refereshing.log');
        }

        Mage::dispatchEvent('shippingbox_clean_after');

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ffdxshippingbox')->__('Cleaning has been done.'));
        $this->_redirect('*/*/index');
    }

    /**
     * save action redirect to check_one
     */
    public function saveAction()
    {
        $this->_forward('check_one');
    }
}