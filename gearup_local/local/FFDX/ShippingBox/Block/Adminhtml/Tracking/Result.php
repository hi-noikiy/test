<?php

/**
 * Class FFDX_ShippingBox_Block_Adminhtml_Tracking_Result
 */
class FFDX_ShippingBox_Block_Adminhtml_Tracking_Result extends Mage_Core_Block_Template
{

    protected $collection;

    /**
     * construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = Mage::helper('ffdxshippingbox')->__('Tracking Data');
        $this->setTemplate('ffdxshippingbox/result.phtml');
    }

    /**
     * display one tracking use function check()
     *
     * @return array|null
     */
    public function getTrackingHistoryOld()
    {
        $result = '';
        if (!isset($this->collection)) {
            $trackingNumber = Mage::registry('tracking_number');
            $this->collection = new Varien_Data_Collection();

            if ($trackingNumber) {
                $result = Mage::helper('ffdxshippingbox')->getDataFromApi($trackingNumber);
                if ($result) {

                    $items = $result['Event'];

                    /*try {
                        Mage::getSingleton('ffdxshippingbox/observer')->save($items);
                    } catch (Exception $e) {
                        Mage::log($e . 'ffdxshipping_box_check_one_save.log');
                    }*/

                    foreach ($items as $item) {
                        $signedBy = $item['Remarks'];
                        $referenceNumber = $item['ReferenceNumber'];
                        $eventTime = $item['EventDateTime'];

                        $eventTime = new DateTime($eventTime);
                        $eventHour = $eventTime->format('H:i');
                        $eventDate = $eventTime->format('d-m-Y');

                        $location = $item['UpdateEntityLocationName'];
                        $activityNumber = $item['EventID'];

                        $source = Mage::getModel('ffdxshippingbox/source_event_code');
                        $eventCodes = $source->getMap();

                        $event = isset($eventCodes[$activityNumber]) ? $eventCodes[$activityNumber] : 'Unknown';

                        if (empty($location)) {
                            $location = $this->collection->getLastItem()->getLocation();
                        }

                        $entry = new Varien_Object(
                            array(
                                'signed_by' => $signedBy,
                                'reference_number' => $referenceNumber,
                                'event_date' => $eventDate,
                                'event_hour' => $eventHour,
                                'location' => $location,
                                'event' => $event
                            )
                        );
                        $this->collection->addItem($entry);
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ffdxshippingbox')->__('Track does not exist in FFDX data base.'));
                    return null;
                }
            } else {
                return null;
            }
        }

        return $this->collection;
    }

    
    
    
  /**
     * display tracking history
     *
     * @return array|null
     */
    public function getTrackingHistory()
    {
        $result = '';
        if (!isset($this->collection)) {
            $trackingNumber = Mage::registry('tracking_number');
            $this->collection = new Varien_Data_Collection();
            if ($trackingNumber) {
                $result = Mage::helper('ffdxshippingbox')->getDataFromApi($trackingNumber);
                if ($result) {
                    if(isset($result['DateTime']))
                        $items[] = $result; 
                    else
                        $items = $result;
                    
                    foreach ($items as $item) {
                        $entry = new Varien_Object(
                            array(
                                'signed_by' => $item['Note'],  //$signedBy,
                                'reference_number' => $trackingNumber, //$referenceNumber,
                                'event_date' => str_replace('/','-' ,'30/12/2016'),
                                'event_hour' => explode(' ',$item['DateTime'])[1],
                              //  'location' => 'DU',//$location,
                                'event' => $item['Event']//$event
                            )
                        );

                        $this->collection->addItem($entry);
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ffdxshippingbox')->__('Track does not exist in FFDX data base.'));
                    return null;
                }
            } else {
                return null;
            }
        }
        return $this->collection;
    }    
    
    /**
     * get all items in reversed order
     * @return array
     */
    public function getItems()
    {
        $result = '';
        if ($this->getTrackingHistory()) {
            $result = array_reverse($this->getTrackingHistory()->getItems());
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * get Last item to get 'signed_by' for template
     * @return mixed
     */
    public function getLastItem()
    {
        $result = '';
        if ($this->getTrackingHistory()) {
            $result = $this->getTrackingHistory()->getLastItem();
        } else {
            $result = null;
        }
        return $result;
    }
}