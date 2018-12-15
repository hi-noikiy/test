<?php
class EM_LayeredNavigation_Helper_Breadcrumbs extends Mage_Catalog_Helper_Data
{
	/**
     * Return current category path or get it from current category
     * and creating array of categories|product paths for breadcrumbs
     *
     * @return string
     */
    public function getBreadcrumbPath()
    {
        if (!$this->_categoryPath) {

            $path = array();
            if ($category = $this->getCategory()) {
                $pathInStore = $category->getPathInStore();
                $pathIds = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();

                // add category path breadcrumb
                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path['category'.$categoryId] = array(
                            'label' => $categories[$categoryId]->getName(),
                            'link' => $this->_isCategoryLink($categoryId) ? $categories[$categoryId]->getUrl() : ''
                        );
                    }
                }
            }

            

            $this->_categoryPath = $path;
        }
        return $this->_categoryPath;
    }
	
	/**
     * Return current category object
     *
     * @return Mage_Catalog_Model_Category|null
     */
    public function getCategory()
    {
		if($catId = Mage::app()->getRequest()->getParam('cat'))
			return Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($catId);
        return Mage::registry('current_category');
    }
}
