<?php
class EM_Galagearboxsettings_Galagearboxsettings 
{
	public function get_grid_thumb_width()
	{
		return Mage::getStoreConfig('galagearbox/image/grid_thumb_width');
	}
	public function get_grid_thumb_height()
	{
		return Mage::getStoreConfig('galagearbox/image/grid_thumb_height');
	}
	
	public function get_listing_thumb_width()
	{
		return Mage::getStoreConfig('galagearbox/image/listing_thumb_width');
	}
	public function get_listing_thumb_height()
	{
		return Mage::getStoreConfig('galagearbox/image/listing_thumb_height');
	}
	
	public function get_base_image_width()
	{
		return Mage::getStoreConfig('galagearbox/image/base_image_width');
	}
	public function get_base_image_height()
	{
		return Mage::getStoreConfig('galagearbox/image/base_image_height');
	}
	
	public function get_thumb_base_width()
	{
		return Mage::getStoreConfig('galagearbox/image/thumb_base_width');
	}
	public function get_thumb_base_height()
	{
		return Mage::getStoreConfig('galagearbox/image/thumb_base_height');
	}
	
	public function get_related_width()
	{
		return Mage::getStoreConfig('galagearbox/image/related_width');
	}
	public function get_related_height()
	{
		return Mage::getStoreConfig('galagearbox/image/related_height');
	}
	
	public function get_crosssell_width()
	{
		return Mage::getStoreConfig('galagearbox/image/crosssell_width');
	}
	public function get_crosssell_height()
	{
		return Mage::getStoreConfig('galagearbox/image/crosssell_height');
	}
	
	public function get_upsell_width()
	{
		return Mage::getStoreConfig('galagearbox/image/upsell_width');
	}
	public function get_upsell_height()
	{
		return Mage::getStoreConfig('galagearbox/image/upsell_height');
	}
	
	public function get_widget_width()
	{
		return Mage::getStoreConfig('galagearbox/image/widget_width');
	}
	public function get_widget_height()
	{
		return Mage::getStoreConfig('galagearbox/image/widget_height');
	}
	public function get_widget_width_product_home()
	{
		return Mage::getStoreConfig('galagearbox/image/widget_width_product_home');
	}
	public function get_widget_height_product_home()
	{
		return Mage::getStoreConfig('galagearbox/image/widget_height_product_home');
	}
	public function get_cart_sidebar_width()
	{
		return Mage::getStoreConfig('galagearbox/image/cart_sidebar_width');
	}
	public function get_cart_sidebar_height()
	{
		return Mage::getStoreConfig('galagearbox/image/cart_sidebar_height');
	}
	public function get_wishlist_sidebar_width()
	{
		return Mage::getStoreConfig('galagearbox/image/wishlist_sidebar_width');
	}
	public function get_wishlist_sidebar_height()
	{
		return Mage::getStoreConfig('galagearbox/image/wishlist_sidebar_height');
	}
	public function get_lastest_review_width()
	{
		return Mage::getStoreConfig('galagearbox/image/lastest_review_width');
	}
	public function get_lastest_review_height()
	{
		return Mage::getStoreConfig('galagearbox/image/lastest_review_height');
	}
	public function get_compare_width()
	{
		return Mage::getStoreConfig('galagearbox/image/compare_width');
	}
	public function get_compare_height()
	{
		return Mage::getStoreConfig('galagearbox/image/compare_height');
	}
	public function get_bgbody()
	{
		return Mage::getStoreConfig('galagearbox/image/bg_body');
	}
	public function get_bgcolorbody()
	{
		return Mage::getStoreConfig('galagearbox/image/bgcolor_body');
	}
}