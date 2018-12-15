<?php
class EM_Onestepcheckout_indexController extends Mage_Core_Controller_Front_Action
{
	public function reviewAction() {
		$this->loadLayout();
		echo $this->getLayout()->getBlock('checkout.onepage.review.info')->toHtml();
	}
}