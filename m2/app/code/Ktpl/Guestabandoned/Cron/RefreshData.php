<?php

namespace Ktpl\Guestabandoned\Cron;

class RefreshData {

    protected $date;
    protected $_quoteCollectrionFac;
    protected $_salesCollectionFac;

    /**
     * 
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectrionFac
     * @param \Magento\Sales\Model\ResourceModel\Sales\CollectionFactory $saleCollection
     */
    function __construct(\Magento\Framework\Stdlib\DateTime\DateTime $date, 
            \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectrionFac, \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory $saleCollection
    ) {
        $this->date = $date;
        $this->_quoteCollectrionFac = $quoteCollectrionFac;
        $this->_salesCollectionFac = $saleCollection;
    }

    public function execute() {

        $fromDate = $this->date->date('Y-m-d 00:00:00');
        $quotec = $this->_quoteCollectrionFac->create();
        $quotec->addFieldToFilter('main_table.created_at', array('from' => $fromDate, true))
                ->addFieldToFilter('main_table.is_active', array('eq' => '1'));
        $quotec->getSelect()->joinleft('quote_address', 'main_table.entity_id = quote_address.quote_id && quote_address.address_type = "shipping"', array('telephone'));
        $quotec->addFieldToFilter('quote_address.telephone', array('notnull' => true));
        $orderc = $this->_salesCollectionFac->create();
        $orderc->addFieldToFilter('main_table.created_at', array('from' => $fromDate, true));

        foreach ($quotec as $qc) {
            foreach ($orderc as $oc) {
                if ($qc->getCustomerEmail() == $oc->getCustomerEmail()) {
                    $qc->setData('is_active', 0);
                    $qc->save();
                }                
            }
        }
    }
}
