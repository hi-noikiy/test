<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

    	/*
    	 * Load an object by id
    	 * Request looking like:
    	 * http://site.com/eshopsync?id=15
    	 *  or
    	 * http://site.com/eshopsync/id/15
    	 */
    	/*
		$eshopsync_id = $this->getRequest()->getParam('id');

  		if($eshopsync_id != null && $eshopsync_id != '')	{
			$eshopsync = Mage::getModel('eshopsync/eshopsync')->load($eshopsync_id)->getData();
		} else {
			$eshopsync = null;
		}
		*/

		 /*
    	 * If no param we load a the last created item
    	 */
    	/*
    	if($eshopsync == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$eshopsyncTable = $resource->getTableName('eshopsync');

			$select = $read->select()
			   ->from($eshopsyncTable,array('eshopsync_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;

			$eshopsync = $read->fetchRow($select);
		}
		Mage::register('eshopsync', $eshopsync);
		*/


		$this->loadLayout();
		$this->renderLayout();
    }
}
