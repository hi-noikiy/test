<?php

class Ktpl_RewardPoints_Model_Cron {

    const NO_OF_MAIL = 100;
    /**
     * Process transactions (holding, expire) by cron
     */
    public function sendrewards() {
        try {
            Varien_Profiler::start('KREWARD_CRON::sendrewards');
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $write = $resource->getConnection('core_write');
            $rewards = $read->query("select * from reward_schedule");
           
            $i=1;
            foreach($rewards as $r)
            {
                $customer = Mage::getModel('customer/customer')->load($r['customer_id']);
                $rData = Mage::getModel('rewardpoints/customer')->load($r['customer_id'], 'customer_id');
               
                $rate = Mage::getSingleton('rewardpoints/rate')->getRate(
                    Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY, $customer->getGroupId(), $customer->getWebsiteId()
                );
           
                if ($rData->getPointBalance() > 0 && $rData->getPointBalance() != '') {
                    
                    
                    $templateId = 18; // Enter you new template ID
                    $senderName = Mage::getStoreConfig('trans_email/ident_support/name');  //Get Sender Name from Store Email Addresses
                    $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');  //Get Sender Email Id from Store Email Addresses
                    $sender = array('name' => $senderName,
                        'email' => $senderEmail);
                    $email_template  = Mage::getModel('core/email_template')->loadDefault($templateId);
        
                    // Set recepient information
                    $recepientEmail = $customer->getEmail();
                    $recepientName = $customer->getName();

                    // Get Store ID     
                    $store = Mage::app()->getStore()->getId();
                    $rs=ceil($rData->getPointBalance()*$rate->getMoney()/$rate->getpoints());
                    // Set variables that can be used in email template
                    $vars = array('customerName' => $customer->getName(), 'point' => $rData->getPointBalance(), 'rs' => $rs);

                    // Send Transactional Email
                     if(!Mage::getModel('core/email_template')
                            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $store)) {
                        Mage::log($recepientEmail, null, 'ktpl_rewards_fail-'.date("Y-m-d").'.log');
                    }
                    else{
                        Mage::log($recepientEmail, null, 'ktpl_rewards_success-'.date("Y-m-d").'.log');
                        $write->delete("reward_schedule",'customer_id="'.$customer->getId().'"');
                    }
                }
                $i++;
                if($i >= self::NO_OF_MAIL ){ break;} 
            }
        } catch (Exception $e) {
            Mage::printException($e);
        }
      
        Varien_Profiler::stop('KREWARD_CRON::sendrewards');
    }
    
    public function createrewards() {
        try {
            Varien_Profiler::start('KREWARD_CRON::createrewards');
            $customers = Mage::getModel("customer/customer")->getCollection()->addAttributeToSelect('*');
            $customers->getSelect()->joinLeft('rewardpoints_customer', 'e.entity_id = rewardpoints_customer.customer_id', array('*'));
            
            $resource = Mage::getSingleton('core/resource');
            $write = $resource->getConnection('core_write');
            $table = $resource->getTableName('reward_schedule');
                
            foreach ($customers as $customer) {
                if ($customer->getPointBalance() > 0 && $customer->getPointBalance() != '') {
                    $write->insert(
                        "reward_schedule", 
                        array("customer_id" => $customer->getId(), "customer_email" => $customer->getEmail())
                    );
                }
            }
        
        } catch (Exception $e) {
            Mage::printException($e);
        }
      
        Varien_Profiler::stop('KREWARD_CRON::createrewards');
    }

}
