<?php
class EM_Slideshow3_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/slideshow3?id=15 
    	 *  or
    	 * http://site.com/slideshow3/id/15 	
    	 */
    	/* 
		$slideshow3_id = $this->getRequest()->getParam('id');

  		if($slideshow3_id != null && $slideshow3_id != '')	{
			$slideshow3 = Mage::getModel('slideshow3/slideshow3')->load($slideshow3_id)->getData();
		} else {
			$slideshow3 = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($slideshow3 == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$slideshow3Table = $resource->getTableName('slideshow3');
			
			$select = $read->select()
			   ->from($slideshow3Table,array('slideshow3_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$slideshow3 = $read->fetchRow($select);
		}
		Mage::register('slideshow3', $slideshow3);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}