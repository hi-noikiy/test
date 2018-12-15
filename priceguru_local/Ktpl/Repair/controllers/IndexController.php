<?php

class Ktpl_Repair_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function postAction()
    {
        //echo 'sag'; exit;
        if ($data = $this->getRequest()->getPost()) {

            if($orderId = $this->getRequest()->getParam('order_id')) {
                $data['order_id'] = $orderId;
            }

            // Look up customer details to see if there's an existing requester_id assigned
            $requesterId = null;
            $requesterEmail = trim($data['email']);
            $requesterName = trim($data['name']);

            $customer = null;
            if(Mage::getModel('customer/customer')->getSharingConfig()->isWebsiteScope()) {
                // Customer email address can be used in multiple websites so we need to
                // explicitly scope it
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId($data['website_id'])
                    ->loadByEmail($data['email']);
            } else {
                // Customer email is global, so no scoping issues
                $customer = Mage::getModel('customer/customer')
                    ->loadByEmail($data['email']);
            }

            // Check if a valid customer has been loaded
            if($customer->getId()) {
                // Provided for future expansion, where we might want to store the customer's requester ID for
                // convenience; for now it simply returns null
                $requesterId = $customer->getZendeskRequesterId();

                // If the requester name hasn't already been set, then set it to the customer name
                if(strlen($requesterName) == 0) {
                    $requesterName = $customer->getName();
                }
            }

            if($requesterId == null) {
                // See if the requester already exists in Zendesk
                try {
                    $user = Mage::getModel('zendesk/api_requesters')->find($requesterEmail);
                } catch (Exception $e) {
                    // Continue on, no need to show an alert for this
                    $user = null;
                }

                if($user) {
                    $requesterId = $user['id'];
                } else {
                    // Create the requester as they obviously don't exist in Zendesk yet
                    try {
                        // First check if the requesterName has been provided, since we need that to create a new
                        // user (but if one exists already then it doesn't need to be filled out in the form)
                        if(strlen($requesterName) == 0) {
                            throw new Exception('Requester name not provided for new user');
                        }

                        // All the data we need seems to exist, so let's create a new user
                        $user = Mage::getModel('zendesk/api_requesters')->create($requesterEmail, $requesterName);
                        $requesterId = $user['id'];
                    } catch(Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getCode() . ': ' . $e->getMessage());
                        Mage::register('zendesk_create_data', $data, true);
                        $this->_redirect('*/*/create');
                    }
                }
            }

            try {
//                $admin = Mage::getSingleton('admin/session')->getUser();
//                $submitter = Mage::getModel('zendesk/api_users')->find($admin->getEmail());
//
//                if (!$submitter) {
//                    // Default to the user set in the agent email field under Configuration
//                    $submitter = Mage::getModel('zendesk/api_users')->me();
//                }

                $ticket = array(
                    'ticket' => array(
                        'requester_id' => $requesterId,
                        'submitter_id' => 9381882225,
                        'subject' => $data['orderid'],
                        'status' => 'new',
                        'priority' => 'normal',
                        'fields'=>array('39575429'=>$data['orderid']),
                        'comment' => array(
                            'value' => $data['description']
                        )
                    )
                );

                if(isset($data['type']) && strlen(trim($data['type'])) > 0) {
                    $ticket['ticket']['type'] = $data['type'];
                }

                if( ($fieldId = Mage::getStoreConfig('zendesk/frontend_features/order_field_id')) && isset($data['order']) && strlen(trim($data['order'])) > 0) {
                    $ticket['ticket']['fields'] = array(
                        'id' => $fieldId,
                        'value' => $data['order']
                    );
                }

                $response = Mage::getModel('zendesk/api_tickets')->create($ticket);

                
                Mage::getSingleton('core/session')->addSuccess("Your repair query has been generated.");
            } catch(Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getCode() . ': ' . $e->getMessage());
                Mage::register('zendesk_create_data', $data, true);
            }
        }
        $this->_redirect('*/*/index');
    }

}