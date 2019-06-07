<?php
/**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Gearup_Competera_Block_Adminhtml_Competera_Pricechange_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('competeraPricechangeGrid');
        $this->setDefaultSort('pricechange_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $historyId = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('competera/pricechangelog')->getCollection()
                                ->addFieldToFilter('history_id', $historyId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();
        $store = $this->_getStore();

        $this->addColumn('pricechange_id', array(
            'header'    => Mage::helper('competera')->__('ID'),
            'width'     => '50px',
            'index'     => 'pricechange_id',
            'type'  => 'number',
        ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '120px',
                'index' => 'sku',
        ));

        $this->addColumn('part_number',
            array(
                'header'=> Mage::helper('catalog')->__('Part Number'),
                'width' => '120px',
                'index' => 'part_number',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('competera')->__('Name'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('price_type', array(
            'header'    => Mage::helper('competera')->__('Price Type'),
            'align'     => 'left',
            'index'     => 'price_type',
        ));

        $this->addColumn('old_price', array(
            'header'    => Mage::helper('competera')->__('Old Price'),
            'type'      => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'old_price',
        ));

        $this->addColumn('new_price', array(
            'header'    => Mage::helper('competera')->__('New Price'),
            'type'      => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'new_price',
        ));
        
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem('entity_id', array(
        'label'=> Mage::helper('competera')->__('Update Price'),
        'url'  => $this->getUrl('*/*/massUpdate', array('' => ''))
        ));
        return $this;
    }
    protected function _afterLoadCollection()
    {
        $priceArr = Mage::helper('competera')->getCompeteraPrice();
        foreach ($this->_collection as $item) {
            $item->setNewprice($priceArr[$item->getSku()]);
            $curPrice = ($item->getSpecialPrice() != '' ? $item->getSpecialPrice() : $item->getPrice());
            $suggestedPrice = $priceArr[$item->getSku()];
            if($curPrice > $suggestedPrice) {
                $count2 = 0;
                $count1 = $suggestedPrice / $curPrice;
                if($count1 != 1)
                    $count2 = $count1 * 100;
                $count = number_format($count2, 2);
                $item->setPricediff($count.'%');
            } else {
                $count2 = 0;
                $count1 = $curPrice / $suggestedPrice;
                if($count1 != 1)
                    $count2 = $count1 * 100;
                $count = number_format($count2, 2);
                $item->setPricediff($count.'%');
            }
        }
        return $this;
    }
}
