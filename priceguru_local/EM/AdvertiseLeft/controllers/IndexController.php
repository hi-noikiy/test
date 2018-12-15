<?php
class EM_AdvertiseLeft_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/advertiseleft?id=15 
    	 *  or
    	 * http://site.com/advertiseleft/id/15 	
    	 */
    	/* 
		$advertiseleft_id = $this->getRequest()->getParam('id');

  		if($advertiseleft_id != null && $advertiseleft_id != '')	{
			$advertiseleft = Mage::getModel('advertiseleft/advertiseleft')->load($advertiseleft_id)->getData();
		} else {
			$advertiseleft = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($advertiseleft == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$advertiseleftTable = $resource->getTableName('advertiseleft');
			
			$select = $read->select()
			   ->from($advertiseleftTable,array('advertiseleft_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$advertiseleft = $read->fetchRow($select);
		}
		Mage::register('advertiseleft', $advertiseleft);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}