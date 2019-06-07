<?php
/**
 * 
 */

class Gearup_Blog_Block_Menu_Sidebar extends AW_Blog_Block_Menu_Sidebar
{
	public function getAllPosts()
	{
	    /*$collection = Mage::getModel('blog/post')->getCollection()
	                ->addFieldToSelect('post_id');     
	           
	    return count($collection);*/

	    $collection = Mage::getModel('blog/blog')->getCollection()
                    ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
                    ->addStoreFilter();
                    
        return count($collection);
	}
	
	public function getCategoryPosts($catId)
	{
	    $resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read'); 
	    $collection = $readConnection->fetchCol("SELECT * FROM `aw_blog_post_cat` INNER JOIN `aw_blog` ON `aw_blog_post_cat`.`post_id` = `aw_blog`.`post_id` WHERE `aw_blog_post_cat`.`cat_id` = $catId AND `aw_blog`.`status` = 1");     
	           
	    return count($collection);
	}
}