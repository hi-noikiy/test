<?php
$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('repair_to_center')};
CREATE TABLE {$this->getTable('repair_to_center')} (
  `repair_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_time` datetime DEFAULT NULL,
  `increment_id` varchar(50) NOT NULL,
  `customer` text NOT NULL,
  `product` text NOT NULL,
  `problem_description` text NULL,
  `wholesaler` int(11) NOT NULL,
  `serial_no` text NULL,
  `pickup_option` int(1) NULL,
  `pickup_address` varchar(512) NOT NULL,
  `is_pickup` int(1) NULL,
  `pickup_date` datetime DEFAULT NULL,
  `status` int(1) NULL,
  PRIMARY KEY (`repair_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001001;

");
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('repair_to_center')} ADD CONSTRAINT 
    `FK_REPAIR_CENTER_INCREMENT_ID_SALES_ORDER_INCREMENT_ID` 
    FOREIGN KEY (`increment_id`) REFERENCES `sales_flat_order` (`increment_id`) ON DELETE CASCADE ON UPDATE CASCADE;
   
SQLTEXT;

$installer->run($sql);

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('repair_to_customer')};
CREATE TABLE {$this->getTable('repair_to_customer')} (
  `repair_customer_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `repair_center_id` int(11) unsigned NOT NULL,
  `customer` text NOT NULL,
  `product` text NOT NULL,
  `service_order_no` text NULL,
  `dispatch_date` datetime DEFAULT NULL,
  `diagnostic` int(1) NULL,
  `supplier_comments` text NULL,
  `warranty_status` int(1) NULL,
  `client_informed` int(1) NULL,
   `comments` text NULL,
  `supplier_informed` int(1) NULL,
  `replacement` int(1) NULL,
  `collect_date` datetime DEFAULT NULL,
  `leadtime` varchar(255) NOT NULL,
  `is_pickup` int(1) NULL,
  `status` int(1) NULL,
    
  PRIMARY KEY (`repair_customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

");

$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('repair_to_customer')} ADD CONSTRAINT 
    `FK_REPAIR_CUSTOMER_REPAIR_CENTER_ID_REPAIR_CENTER_REPAIR_ID` 
    FOREIGN KEY (`repair_center_id`) REFERENCES `repair_to_center` (`repair_id`) ON DELETE CASCADE ON UPDATE CASCADE;
   
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 