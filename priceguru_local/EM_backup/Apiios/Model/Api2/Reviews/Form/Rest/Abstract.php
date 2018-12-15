<?php
class EM_Apiios_Model_Api2_Reviews_Form_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{
	const API_ADD_REVIEWS = 'API add new reviews';

    protected function _retrieve(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
		$result	=	array();

	// ------------- list rating ---------------------
		$ratingCollection = Mage::getModel('rating/rating')
            ->getResourceCollection()
            //->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName($this->_getStore()->getId())
            ->setStoreFilter($this->_getStore()->getId())
            ->load()
            ->addOptionToItems();

		foreach($ratingCollection->getItems() as $item){
			$tmp	=	$item->getData();
			$tmp['options']	=	array();
			foreach($item->getOptions() as $opt){
				$tmp['options'][]	=	$opt->getData();
			}
			$rating[]	=	$tmp;
		}
		$result['form_review']['rating']	=	$rating;

		$form	=	array(
			array(
				'label'	=>	Mage::Helper('apiios')->__('Nickname'),
				'name'	=>	Mage::Helper('apiios')->__('nickname'),
				'required' 	=>	true,
				'type' 	=>	'text'
			),
			array(
				'label'	=>	Mage::Helper('apiios')->__('Summary of Your Review'),
				'name'	=>	Mage::Helper('apiios')->__('title'),
				'required' 	=>	true,
				'type'	=>	'text'
			),
			array(
				'label'	=>	Mage::Helper('apiios')->__('Review'),
				'name'	=>	Mage::Helper('apiios')->__('detail'),
				'required' 	=>	true,
				'type'	=>	'textarea'
			)
		);
		$result['form_review']['input']	=	$form;

		return $result;
    }
	
	protected function _initProduct($id)
    {
        Mage::dispatchEvent('review_controller_product_init_before', array('controller_action'=>$this));
        //$categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $id;

        $product = $this->_loadProduct($productId);
        if (!$product) {
            return false;
        }

       /* if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }*/

        try {
            Mage::dispatchEvent('review_controller_product_init', array('product'=>$product));
            Mage::dispatchEvent('review_controller_product_init_after', array(
                'product'           => $product,
                'controller_action' => $this
            ));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $product;
    }
	
	protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->_getStore()->getId())
            ->load($productId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        return $product;
    }

    /**
     * Create new review
     *
     * @param array $params
     */
    protected function _createReview(array $params){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
		if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $params;
            $rating = $data['ratings'];
            $product_id = $data['product'];
			unset($data['product']);
        }
		
        if (($product = $this->_initProduct($product_id)) && !empty($data)) {
            $session    = Mage::getSingleton('core/session');
            /* @var $session Mage_Core_Model_Session */
            $review     = Mage::getModel('review/review')->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId($this->_getStore()->getId())
                        ->setStores(array($this->_getStore()->getId()))
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                    }
					
                    $review->aggregate();
					$check  = 'success';
                    $msg	=	Mage::Helper('apiios')->__('Your review has been accepted for moderation.');
                }
                catch (Exception $e) {
                    $session->setFormData($data);
					$check  = 'error';
                    $msg	=	Mage::Helper('apiios')->__('Unable to post the review.');
                }
            }
            else {
				$check  = 'error';
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $tmp_msg[]	=	$errorMessage;
                    }
					$msg	=	$tmp_msg;
                }
                else {
                    $msg	=	Mage::Helper('apiios')->__('Unable to post the review.');
                }
            }
        }else{
			$check 	= 'error';
			$msg 	= Mage::Helper('apiios')->__('Product ID is not available !.');
		}

		$result['result']['check']	=	$check;

		$this->_successMessage(
			$msg,
			Mage_Api2_Model_Server::HTTP_OK,
			$result
		);
	}

    /**
     * Create new review (for ios)
     *
     * @param array $data
     * @return string|void
     */
    protected function _create($data){
        $this->_createReview($data);
        $this->_render($this->getResponse()->getMessages());
    }

    /**
     * Create new review (for android)
     *
     * @param array $data
     */
    protected function _multiCreate($data){
        $this->_createReview($data[0]);
    }
}
?>