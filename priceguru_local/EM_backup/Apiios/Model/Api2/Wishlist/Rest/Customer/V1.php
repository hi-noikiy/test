<?php
class EM_Apiios_Model_Api2_Wishlist_Rest_Customer_V1 extends EM_Apiios_Model_Api2_Products
{
    /**
     * Retrieve wishlist object
     * @param array $data
     * @param int|null $wishlistId
     * @return Mage_Wishlist_Model_Wishlist|bool
     */
    protected function _getWishlist($data, $wishlistId = null)
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }

        try {
            if (!$wishlistId) {
                $wishlistId = isset($data['wishlist_id']) ? $data['wishlist_id'] : '';
            }
            $customerId = $this->getApiUser()->getUserId();
            /* @var Mage_Wishlist_Model_Wishlist $wishlist */
            $wishlist = Mage::getModel('wishlist/wishlist');
            if ($wishlistId) {
                $wishlist->load($wishlistId);
            } else {
                $wishlist->loadByCustomer($customerId, true);
            }

            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                $wishlist = null;
                $this->_errorMessage(Mage::helper('wishlist')->__("Requested wishlist doesn't exist"),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }

            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            $this->_errorMessage($e->getMessage(),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return false;
        } catch (Exception $e) {
            $this->_errorMessage(Mage::helper('wishlist')->__('Wishlist could not be created.'),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return false;
        }

        return $wishlist;
    }

    /**
     * Add the item to wish list
     *
     * @param array $data
     * @return Mage_Core_Controller_Varien_Action|void
     */
    protected function _addItemToWishList($data)
    {
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $wishlist = $this->_getWishlist($data);
        if (!$wishlist) {
            $this->_errorMessage(Mage::helper('apiios')->__("Not found"),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return $this;
        }

        $productId = (int)$data['product'];
        if (!$productId) {
            $this->_errorMessage(Mage::helper('wishlist')->__("Cannot specify product."),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return $this;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->_errorMessage(Mage::helper('wishlist')->__("Cannot specify product."),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return $this;
        }

        try {
            $data['store_id'] = $this->_getStore()->getId();
            $buyRequest = new Varien_Object($data);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                $this->_errorMessage($result,Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                return $this;
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            Mage::helper('wishlist')->calculate();

            $this->_successMessage(
                Mage::helper('apiios')->__('%s has been added to your wishlist.',$product->getName()),
                Mage_Api2_Model_Server::HTTP_OK,
                array('id'=>$wishlist->getId())
            );
            return $this;
        } catch (Mage_Core_Exception $e) {
            $this->_errorMessage(Mage::helper('wishlist')->__('An error occurred while adding item to wishlist: %s', $e->getMessage()),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return $this;
        }
        catch (Exception $e) {
            $this->_errorMessage(Mage::helper('wishlist')->__('An error occurred while adding item to wishlist.'),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return $this;
        }
        return $this;
    }

    /**
     * Add wish list ( for ios ). Method : POST.
     *
     * @param array $data
     * @return string|void
     */
    public function _create($data){
        $this->_addItemToWishList($data);
        $this->_render($this->getResponse()->getMessages());
    }

    /**
     * Add wish list ( for android ). Method : POST.
     *
     * @param array $data
     * @return string|void
     */
    protected function _multiCreate($data){
        $this->_addItemToWishList($data[0]);
    }

    /**
     * Get wish list item. Method : GET.
     *
     * @return array
     */
    public function _retrieve(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $data = $this->getRequest()->getParam('wishlist_id');
        $collection = clone $this->_getWishlist($data)->getItemCollection();
        $collection->clear();
        $collection->setInStockFilter(true)
            ->setOrder('added_at','DESC');
        $productsJson = array();
        $result = array();
        if($collection->count() > 0){
            $thumbnailWidth = $this->getRequest()->getParam('thumbnail_width',25);
            $thumbnailHeight = $this->getRequest()->getParam('thumbnail_height',25);
            foreach($collection as $item){
                $product = $item->getProduct();
                $this->_setProduct($product);
                $itemData = $this->_prepareProductForResponse($product,array('thumbnail'=>array('width'=>$thumbnailWidth,'height'=>$thumbnailHeight)));
                $itemData['item_id'] = $item->getId();
                $productsJson[] = $itemData;
            }
            $result['items'] = $productsJson;
            $result['count'] = $collection->count();
        } else {
            $result['count'] = 0;
            $result['empty_message'] = Mage::helper('apiios')->__('You have no items in your wish list.');
        }
        return $result;
    }

    /**
     * Delete wish list item. Method : DELETE.
     */
    public function _delete(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $itemId = $this->getRequest()->getParam('item_id');
        $item = Mage::getModel('wishlist/item')->load($itemId);
        if (!$item->getId()) {
            $this->_errorMessage(Mage::helper('apiios')->__("Cannot get wish list item."),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return $this;
        }
        $wishlist = $this->_getWishlist(array(),$item->getWishlistId());
        if (!$wishlist) {
            $this->_errorMessage(Mage::helper('apiios')->__("Cannot get wish list item."),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            return $this;
        }
        try {
            $item->delete();
            $wishlist->save();
			$this->_successMessage(
                Mage::helper('apiios')->__('Wishlist item has been deleted.'),
                Mage_Api2_Model_Server::HTTP_OK,
                array()
            );
        } catch (Mage_Core_Exception $e) {
            $this->_errorMessage(Mage::helper('wishlist')->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage()),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);

        } catch (Exception $e) {
            $this->_errorMessage(Mage::helper('wishlist')->__('An error occurred while deleting the item from wishlist.'),Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        Mage::helper('wishlist')->calculate();
        $this->_render($this->getResponse()->getMessages());
    }
}