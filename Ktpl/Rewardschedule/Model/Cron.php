<?php

namespace Ktpl\Rewardschedule\Model;

class Cron {

    const NO_OF_MAIL = 1;
    protected $logger;
    protected $objectManager;
    protected $scopeConfig;
    private $_transportBuilder;
  
    public function __construct(
        \Psr\Log\LoggerInterface $logger, 
        \Magento\Framework\ObjectManagerInterface$objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
    }

    public function getStorename()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

 public function getStoreEmail()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function createrewards() {
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $joinTable = $resource->getTableName('rewardpoints_customer');
            $connection = $resource->getConnection();
            
            $customers = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
           // $customers->getSelect()->joinLeft('rewardpoints_customer', 'e.entity_id = rewardpoints_customer.customer_id', array('*'));
            $customers->load();
            
            $tableName = $resource->getTableName('reward_schedule'); //gives table name with prefix
            foreach($customers as $customer){
                $sql = "Insert Into " . $tableName . " (customer_id, customer_email) Values ('".$customer->getId()."','".$customer->getEmail()."')";
                $connection->query($sql);
            }
        } catch (\Exception $e) {
             $this->logger->critical($e->getMessage());
        }
    }
    
    public function sendrewards() {
        
          try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('reward_schedule');
                $sql = "SELECT * FROM reward_schedule";
                $rewards = $connection->fetchAll($sql); 
            
           
            $i=1;
            foreach($rewards as $r)
            {
                $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($r['customer_id']);
                //$rData = $objectManager->create('Rewardpoints\Customer\Model\Customer')->load($r['customer_id'], 'customer_id');
               
               // $rate = Mage::getSingleton('rewardpoints/rate')->getRate(
                //    Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY, $customer->getGroupId(), $customer->getWebsiteId()
               // );
           
              //  if ($rData->getPointBalance() > 0 && $rData->getPointBalance() != '') {
                     
                    $templateId = 1; // Enter you new template ID
                    $senderName = $this->getStorename();  //Get Sender Name from Store Email Addresses
                    $senderEmail = $this->getStoreEmail();  //Get Sender Email Id from Store Email Addresses
                    $sender = array('name' => $senderName,'email' => $senderEmail);
                   // $email_template  = Mage::getModel('core/email_template')->loadDefault($templateId);
        
                    // Set recepient information
                    $recepientEmail = 'khodu.vaishnav@krishtechnolabs.com';//$customer->getEmail();
                    $recepientName = $customer->getName();

                    // Get Store ID     
                    //$store = Mage::app()->getStore()->getId();
                    $rs=1;//ceil($rData->getPointBalance()*$rate->getMoney()/$rate->getpoints());
                    // Set variables that can be used in email template
                    $vars = array('customerName' => $customer->getName(), 'point' => 100, 'rs' => $rs);
                    $postObject = new \Magento\Framework\DataObject();
                    $postObject->setData($vars);
                
                     $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
                                        ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 
                                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                                        ->setTemplateVars(['data' => $postObject])
                                        ->setFrom($sender)
                                        ->addTo($recepientEmail)
                                        ->setReplyTo($senderEmail)            
                                        ->getTransport();               
                //$transport->sendMessage();
                    // Send Transactional Email
                     if(!$transport->sendMessage())
                     {
                        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ktpl_rewards_fail-'.date("Y-m-d H:i").'.log');
                        $logger = new \Zend\Log\Logger();
                        $logger->addWriter($writer);
                        $logger->info($recepientEmail);
                        
                    }
                    else{
                        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ktpl_rewards_success-'.date("Y-m-d H:i").'.log');
                        $logger = new \Zend\Log\Logger();
                        $logger->addWriter($writer);
                        $logger->info($recepientEmail);
                        $dsql = "DELETE FROM " . $tableName . " WHERE customer_id = " . $customer->getId();
                        $connection->query($dsql);
                        
                    }
               // }
                $i++;
                if($i >= self::NO_OF_MAIL ){ break;} 
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

}
