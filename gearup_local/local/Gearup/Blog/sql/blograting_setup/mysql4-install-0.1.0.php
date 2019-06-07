<?php
 /**
 * Magento
 *
 * DISCLAIMER
 *
 * Blog review 
 *
 * @category   Gearup
 * @package    Gearup_Blog
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();
$installer->run("insert  into {$this->getTable('rating_entity')}(`entity_id`,`entity_code`) values (null,'blog');");

$rating_entity_id =  $installer->getConnection()->lastInsertId($installer->getTable('rating_entity'));

$installer->run("insert  into {$this->getTable('rating')}(`rating_id`,`entity_id`,`rating_code`,`position`) "
    . "values (null,{$rating_entity_id},'Blog Rate',0);");
    
$rating_id =  $installer->getConnection()->lastInsertId($installer->getTable('rating'));
 
$installer->run("insert  into {$this->getTable('rating_option')}(`option_id`,`rating_id`,`code`,`value`,`position`) "
    . "values (null,{$rating_id},1,1,1),(null,{$rating_id},2,2,2),(null,{$rating_id},3,3,3),(null,{$rating_id},4,4,4),(null,{$rating_id},5,5,5);");

$installer->run("insert  into {$this->getTable('rating_store')}(`rating_id`,`store_id`) values ({$rating_id},0);");

$installer->endSetup();