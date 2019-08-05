<?php
namespace Magecomp\Paymentfee\Model\Quote\Address\Total;

use Magecomp\Paymentfee\Helper\Data as HelperData;
use Magecomp\Paymentfee\Model\System\HandlingTypes;
use Magecomp\Paymentfee\Model\System\Totals;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Payment\Model\Method\Cc;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magecomp\Paymentfee\Model\Quote\Address\Total\Tax as Paymentfeetax;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductFactory;

class Paymentfee extends AbstractTotal {
    /**
     * @var ScopeConfigInterface
     */
    protected $_configScopeConfigInterface;

    /**
     * @var Calculation
     */
    protected $_modelCalculation;

    /**
     * @var StoreManagerInterface
     */
    protected $_modelStoreManagerInterface;

    /**
     * @var Registry
     */
    protected $_frameworkRegistry;

    /**
     * @var TaxFactory
     */
    protected $_totalTaxFactory;

    /**
     * @var Config
     */
    protected $_modelConfig;

    /**
     * @var LoggerInterface
     */
    protected $_logLoggerInterface;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RequestInterface
     */
    protected $_appRequestInterface;

    /**
     * @var Cart
     */
    protected $_modelCart;
	protected $product;
	
	const MAGECOMP_SURCHARGE_ADDRESS_TAX_ADDED = 'magecomp_paymentfee_address_tax_added';

    protected $_paymentfeeAmount = 0;
    protected $_basePaymentfeeAmount = 0;
    protected $_paymentfeeTaxAmount = 0;
    protected $_basePaymentfeeTaxAmount = 0;
    protected $_paymentfeeDescription = '';
	protected $_ccPaymentfeeApplied = false;
	protected $addresstotal;
	protected $_calculationFactory;

    public function __construct(ScopeConfigInterface $configScopeConfigInterface, 
        Calculation $modelCalculation, 
        StoreManagerInterface $modelStoreManagerInterface, 
        Registry $frameworkRegistry, 
        Paymentfeetax $totalTaxFactory, 
        Config $modelConfig, 
        LoggerInterface $logLoggerInterface, 
        HelperData $helperData, 
        RequestInterface $appRequestInterface, 
        Cart $modelCart,
		ProductFactory $product,
		\Magento\Tax\Model\CalculationFactory $calculationFactory)
    {
        $this->_configScopeConfigInterface = $configScopeConfigInterface;
        $this->_modelCalculation = $modelCalculation;
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;
        $this->_frameworkRegistry = $frameworkRegistry;
        $this->_totalTaxFactory = $totalTaxFactory;
        $this->_modelConfig = $modelConfig;
        $this->_logLoggerInterface = $logLoggerInterface;
        $this->_helperData = $helperData;
        $this->_appRequestInterface = $appRequestInterface;
        $this->_modelCart = $modelCart;
		$this->_calculationFactory = $calculationFactory;
		$this->product = $product; 
    }

	public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total)
	{
		parent::collect($quote, $shippingAssignment, $total); 
		$address = $quote->getShippingAddress();
        //$amount = $address->getMcPaymentfeeAmount();
		$amount = $quote->getMcPaymentfeeAmount();
		
        $total->setTotalAmount('paymentfee', $amount);
        $total->setBaseTotalAmount('paymentfee', $amount);
 
 		$total->setMcPaymentfeeAmount($amount);
        $total->setBaseMcPaymentfeeAmount($amount);
		
		$storeId = $quote->getStoreId();
		$paymentfeeTaxClass = $this->_configScopeConfigInterface->getValue('tax/classes/paymentfee_tax_class', ScopeInterface::SCOPE_STORE,$storeId);
		if ($paymentfeeTaxClass) 
		{
			$taxamount = $address->getMcPaymentfeeTaxAmount();
			$basetaxamount = $address->getBaseMcPaymentfeeTaxAmount();
			$newtax = $address->getTaxAmount() + $taxamount;
			$newbasetax = $address->getBaseTaxAmount() + $basetaxamount;
			$address->setTaxAmount($newtax);
			$address->setBaseTaxAmount($newbasetax);
			$address->save();
			
			$amount += $newtax;
			
			$address->setGrandTotal($address->getGrandTotal() + $taxamount);
        	$address->setBaseGrandTotal($address->getBaseGrandTotal() + $taxamount);
		}
		
		$total->setGrandTotal($total->getGrandTotal() + $amount);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $amount);		
		return $this;
    }
	
    // don't use collect but instead call after calculations via event observer
    // so we have access to final tax
    public function calculate($address) 
	{
		$om = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $om->get('Psr\Log\LoggerInterface');
		$storeManager->info('calculate');
        //reset
				
        $address->setMcPaymentfeeAmount(0);
        $address->setBaseMcPaymentfeeAmount(0);
		$address->save();
				
        //we can return if there are no items
        $items = $address->getAllItems();
				
        if (!count($items)) {
				
            return $this;
			
        }
				
        $quote = $address->getQuote();
        $quoteId = $quote->getId();
				
        if (!is_numeric($quoteId)) {
				
            return $this;
        }
				
        $this->_paymentfeeDescription = '';
        //get storeId
        $storeId = $quote->getStoreId();

        //paymentfee
		if($this->_configScopeConfigInterface->getValue('paymentfee/paymentfeefor/enablepaymentfee', ScopeInterface::SCOPE_STORE, $storeId)) 
		{

        	$this->AddPaymentfeeFee($address, $quote);
		}

        //apply conversion base to order currency for paymentfeei
        $this->_paymentfeeAmount = round($this->_basePaymentfeeAmount * $quote->getBaseToQuoteRate(),2);

        //figure out if we need to tax the paymentfee
        $this->calculatePaymentfeeTax($address);

        //apply conversion base to order currency for paymentfee tax
        $this->_paymentfeeTaxAmount = round($this->_basePaymentfeeTaxAmount * $quote->getBaseToQuoteRate(),2);

        //adjust paymentfee amount to be tax free
        if ($this->_configScopeConfigInterface->getValue('tax/calculation/tax_included_in_paymentfee', ScopeInterface::SCOPE_STORE, $storeId)) {
            $this->_basePaymentfeeAmount = $this->_basePaymentfeeAmount - $this->_basePaymentfeeTaxAmount;
            $this->_paymentfeeAmount = $this->_paymentfeeAmount - $this->_paymentfeeTaxAmount;
        }

        //add Paymentfee tax to existing taxes
        $address->setBaseTaxAmount($address->getBaseTaxAmount()+$this->_basePaymentfeeTaxAmount);
        $address->setTaxAmount($address->getTaxAmount()+$this->_paymentfeeTaxAmount);

        //and apply paymentfee in base and order currency
        $address->setMcPaymentfeeAmount($this->_paymentfeeAmount);
        $address->setBaseMcPaymentfeeAmount($this->_basePaymentfeeAmount);
        $address->setMcPaymentfeeTaxAmount($this->_paymentfeeTaxAmount);
        $address->setBaseMcPaymentfeeTaxAmount($this->_basePaymentfeeTaxAmount);

        //update grand totals
        $address->setGrandTotal($address->getGrandTotal() + $this->_paymentfeeAmount + $this->_paymentfeeTaxAmount);
        $address->setBaseGrandTotal($address->getBaseGrandTotal()+$this->_basePaymentfeeAmount + $this->_basePaymentfeeTaxAmount);

        //our paymentfee is not automatically updated on the quote level - do it here
        $quote->setMcPaymentfeeAmount((float) $quote->getMcPaymentfeeAmount()+ $this->_paymentfeeAmount);
        $quote->setBaseMcPaymentfeeAmount((float) $quote->getBaseMcPaymentfeeAmount()+ $this->_basePaymentfeeAmount);
		
        $quote->setGrandTotal($quote->getGrandTotal() + $this->_paymentfeeAmount + $this->_paymentfeeTaxAmount);
        $quote->setBaseGrandTotal($quote->getBaseGrandTotal()+$this->_basePaymentfeeAmount + $this->_basePaymentfeeTaxAmount);

        //save the description
        $address->setMcPaymentfeeDescription($this->_paymentfeeDescription);
        $quote->setMcPaymentfeeDescription($this->_paymentfeeDescription);
        $address->save();
        $quote->save();

		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info("Quote");
		$logger->info($quote->getData());
        return $this;
    }

	//public function fetch(Address $address) 
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total) 
	{
		$this->addresstotal = $total;
		$address = $quote->getShippingAddress();
	
		if($address->getMcPaymentfeeAmount() == '' || $address->getMcPaymentfeeAmount() == 0)
		{
			$this->calculate($quote->getShippingAddress());
		}
		
        $amount = $address->getMcPaymentfeeAmount();
		$title = '';
        if ($amount != 0) 
		{
            //figure out if we need to tax the paymentfee
            $this->calculatePaymentfeeTax($address,true);

            //need to update tax again since it was overwritten
            $address->setTaxAmount($address->getTaxAmount() + round($this->_paymentfeeTaxAmount,2));
            $address->setBaseTaxAmount($address->getBaseTaxAmount()+round($this->_basePaymentfeeTaxAmount,2));

            //stay BC 
            if($address->getMcPaymentfeeDescription()) 
			{
                $title = $address->getMcPaymentfeeDescription();
            } 
			else 
			{
                $title = $address->getQuote()->getMcPaymentfeeDescription();
            }
            $address->addTotal([
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $amount,
            ]);
            $address->save();
        }
		
		return [
            'code' => $this->getCode(),
            'title' => $title,
            'value' => $amount,
            'area' => 'footer',
        ];
		
	   //return $total;
       //return $this;
    }

    public function AddPaymentfeeFee(Address $address, $quote) 
	{
        $storeId = $quote->getStoreId();
        
		//Paymentfee based on Payment Method 1
        if($this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay1/enablepay', ScopeInterface::SCOPE_STORE, $storeId)) 
		{
			$this->AddPaymentPaymentfee1($address,$quote);
		}
		
		//Paymentfee based on Payment Method 2
        if($this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay2/enablepay', ScopeInterface::SCOPE_STORE, $storeId)) 
		{
			$this->AddPaymentPaymentfee2($address,$quote);
		}
		
		//Paymentfee based on Payment Method 3
        if($this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay3/enablepay', ScopeInterface::SCOPE_STORE, $storeId)) 
		{
			$this->AddPaymentPaymentfee3($address,$quote);
		}
		
		//Paymentfee based on Payment Method 4
        if($this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay4/enablepay', ScopeInterface::SCOPE_STORE, $storeId)) 
		{
			$this->AddPaymentPaymentfee4($address,$quote);
		}
		
		//Paymentfee based on Payment Method 5
        if($this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay5/enablepay', ScopeInterface::SCOPE_STORE, $storeId)) 
		{
			$this->AddPaymentPaymentfee5($address,$quote);
		}
	}


    public function calculatePaymentfeeTax($address, $alreadyInclTax = false) 
	{
		try
		{
			$quote = $address->getQuote();
			$storeId = $quote->getStoreId();
			
			$paymentfeeTaxClass = $this->_configScopeConfigInterface->getValue('tax/classes/paymentfee_tax_class', ScopeInterface::SCOPE_STORE,$storeId);
			
			if ($paymentfeeTaxClass) 
			{
				
				$taxCalculationModel = $this->_modelCalculation;
				$request = $taxCalculationModel->getRateRequest($quote->getShippingAddress(), $quote->getBillingAddress(), $quote->getCustomerTaxClassId(), $storeId);
				$request->setStore($this->_modelStoreManagerInterface->getStore());
				
				
				if ($rate = $taxCalculationModel->getRate($request->setProductClassId($paymentfeeTaxClass))) 
				{
					
					
					if (!$this->_configScopeConfigInterface->getValue('tax/calculation/tax_included_in_paymentfee', ScopeInterface::SCOPE_STORE, $storeId) || $alreadyInclTax) 
					{
						
						$this->_basePaymentfeeTaxAmount = $this->_round($address,$this->_basePaymentfeeAmount * ($rate/100), $rate, true, 'base', false);
						$this->_paymentfeeTaxAmount = $this->_round($address,$this->_paymentfeeAmount * ($rate/100) , $rate, true, 'regular', false);
					} 
					else 
					{
						
						$this->_basePaymentfeeTaxAmount = $this->_round($address,$this->_basePaymentfeeAmount-($this->_basePaymentfeeAmount*(1/(1+$rate/100))), $rate, false, 'base', true);
						$this->_paymentfeeTaxAmount = $this->_round($address,$this->_paymentfeeAmount-($this->_paymentfeeAmount*(1/(1+$rate/100))), $rate, false, 'regular', true);
					}
				}
				
				if(!$this->_frameworkRegistry->registry(self::MAGECOMP_SURCHARGE_ADDRESS_TAX_ADDED)) 
				{
					//$this->_paymentfeeTaxAmount = $address->getMcPaymentfeeTaxAmount();
					//$this->_basePaymentfeeTaxAmount = $address->getBaseMcPaymentfeeTaxAmount();
					/*$this->_totalTaxFactory->create()->_saveAppliedTaxes($address,$taxCalculationModel->getAppliedRates($request),$this->_paymentfeeTaxAmount,$this->_basePaymentfeeTaxAmount, $rate);*/
					$this->_frameworkRegistry->register(self::MAGECOMP_SURCHARGE_ADDRESS_TAX_ADDED,true);
					
				}
			}
			
		}
		catch (\Exception $e)
		{
			$om = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $om->get('Psr\Log\LoggerInterface');
			$storeManager->info($e->getMessage());
		}
    }

    public function addPaymentfeeDescription ($newDesc, $newAmount)
    {
        if (empty($this->_paymentfeeDescription)) 
		{
            $this->_paymentfeeDescription = $newDesc;
        } 
		elseif (strpos($this->_paymentfeeDescription, $newDesc) === false) 
		{
            $sign = ($newAmount > 0) ? ' + ' : ' - ';
            $this->_paymentfeeDescription .= $sign . $newDesc;
        }
    }

    protected function _round($address, $tax, $rate, $direction, $type = 'regular',$paymentfeeInclTax = false)
    {
        if ($tax > 0) 
		{
            $delta = 0;
        
		    switch ($this->_modelConfig->getAlgorithm($this->_modelStoreManagerInterface->getStore()->getId())) 
			{
                case Calculation::CALC_TOTAL_BASE:
                    if ($address) 
					{
                        $appliedTaxes = $address->getAppliedTaxes();
                        foreach ($appliedTaxes as $appliedTax) 
						{
                            //only works if paymentfee and address have same tax rate
                            if ($appliedTax['percent'] == $rate) 
							{
                                if ($type == 'base') 
								{
                                    $totalExclTax = $address->getBaseGrandTotal() - $appliedTax['base_amount'] + $this->_basePaymentfeeAmount;
                                    $taxApplied =  $appliedTax['base_amount'];
                                } 
								else 
								{
                                    $totalExclTax = $address->getGrandTotal() - $appliedTax['amount'] + $this->_paymentfeeAmount;
                                    $taxApplied =  $appliedTax['amount'];
                                }
								if ($paymentfeeInclTax) 
								{
									$totalExclTax -= $tax;
								}
                                $totalTax = $totalExclTax * ( $rate / 100);
                                $delta = $totalTax - $taxApplied - $tax;
                            }
                        }
                    }
                //no break;
                default:
                    return $this->_modelCalculation->round($tax + $delta);
                    break;
            }
        }

    }
	
	public function AddPaymentPaymentfee1(Address $address, $quote)
	{
		try
		{
			$storeId = $quote->getStoreId();
			$paymentfeeOn = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeefor/paymentfeeapply', ScopeInterface::SCOPE_STORE, $storeId));
			$applyGroupFilter = $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay1/payfilterenable', ScopeInterface::SCOPE_STORE, $storeId);
            if($applyGroupFilter) 
			{
               $groupFilter = explode(',', $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay1/paygroup', ScopeInterface::SCOPE_STORE, $storeId));
               if(in_array($quote->getCustomerGroupId(), $groupFilter)) 
			   {
                   $apply = true;
               } else {
                   $apply = false;
               }
            } 
			else 
			{
               $apply = true;
            }
			
            if($apply) 
			{
               $data = $quote->getPayment()->getMethod();
			   $paymentMethods = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay1/paymethods', ScopeInterface::SCOPE_STORE, $storeId));
               if(!empty($data)) 
			   {
                  if(in_array($data,$paymentMethods)) 
				  {
					  //add paymentfee
					  //choose between fixed/percent handling types
                      $handlingType =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay1/payfeetype', ScopeInterface::SCOPE_STORE, $storeId);
                      $subtotal = 0;
					  
					  foreach ($paymentfeeOn as $paymentfeeComponent) 
					  {
						  switch ($paymentfeeComponent) 
						  {
							  case Totals::SURCHARGE_ON_SUBTOTAL:
								  $subtotal += $address->getBaseSubtotal();
								  break;
							  case Totals::SURCHARGE_ON_SHIPPING:
								  $subtotal += $address->getBaseShippingAmount();
								  break;
							  case Totals::SURCHARGE_ON_TAX:
								  $subtotal +=$address->getBaseTaxAmount();
								  break;
							  case Totals::SURCHARGE_EXCLUDE_DISCOUNT:
								  $subtotal +=$address->getBaseDiscountAmount();
								  break;
						  }
					  }

					  $paymentfeeFixed =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay1/payratefix', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeRate =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay1/payrateper', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeDesc =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay1/paydesc', ScopeInterface::SCOPE_STORE, $storeId);
                            
					  switch($handlingType)
					  {
						  case AbstractCarrier::HANDLING_TYPE_FIXED:
							  $paymentfeeAmount = $paymentfeeFixed;
							  break;
						  case AbstractCarrier::HANDLING_TYPE_PERCENT:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2);
							  break;
						  case HandlingTypes::HANDLING_TYPE_COMBINED:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2)+$paymentfeeFixed;
							  break;
						  case HandlingTypes::HANDLING_TYPE_MIN:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate / 100, 2);
							  if ($paymentfeeAmount < $paymentfeeFixed) {
								  $paymentfeeAmount = $paymentfeeFixed;
							  }
							  break;                            
                      }
					  //Calculate paymentfee
					  $this->_basePaymentfeeAmount += $paymentfeeAmount;
					  $this->addPaymentfeeDescription($paymentfeeDesc,$paymentfeeAmount);
                    }
                  }
              }
		}
		catch (\Exception $e)
		{
			$this->_logLoggerInterface->error($e);
            $this->_helperData->debug('Error in Magecomp Paymentfee Payment Method: '. $e->getMessage());
		}
	}
	
	public function AddPaymentPaymentfee2(Address $address, $quote)
	{
		try
		{
			$storeId = $quote->getStoreId();
			$paymentfeeOn = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeefor/paymentfeeapply', ScopeInterface::SCOPE_STORE, $storeId));
			$applyGroupFilter = $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay2/payfilterenable', ScopeInterface::SCOPE_STORE, $storeId);
            if($applyGroupFilter) 
			{
               $groupFilter = explode(',', $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay2/paygroup', ScopeInterface::SCOPE_STORE, $storeId));
               if(in_array($quote->getCustomerGroupId(), $groupFilter)) 
			   {
                   $apply = true;
               } else {
                   $apply = false;
               }
            } 
			else 
			{
               $apply = true;
            }
			
            if($apply) 
			{
               $data = $quote->getPayment()->getMethod();
			   $paymentMethods = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay2/paymethods', ScopeInterface::SCOPE_STORE, $storeId));
               if(!empty($data)) 
			   {
                  if(in_array($data,$paymentMethods)) 
				  {
					  //add paymentfee
					  //choose between fixed/percent handling types
                      $handlingType =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay2/payfeetype', ScopeInterface::SCOPE_STORE, $storeId);
                      $subtotal = 0;
					  
					  foreach ($paymentfeeOn as $paymentfeeComponent) 
					  {
						  switch ($paymentfeeComponent) 
						  {
							  case Totals::SURCHARGE_ON_SUBTOTAL:
								  $subtotal += $address->getBaseSubtotal();
								  break;
							  case Totals::SURCHARGE_ON_SHIPPING:
								  $subtotal += $address->getBaseShippingAmount();
								  break;
							  case Totals::SURCHARGE_ON_TAX:
								  $subtotal +=$address->getBaseTaxAmount();
								  break;
							  case Totals::SURCHARGE_EXCLUDE_DISCOUNT:
								  $subtotal +=$address->getBaseDiscountAmount();
								  break;
						  }
					  }

					  $paymentfeeFixed =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay2/payratefix', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeRate =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay2/payrateper', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeDesc =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay2/paydesc', ScopeInterface::SCOPE_STORE, $storeId);
                            
					  switch($handlingType)
					  {
						  case AbstractCarrier::HANDLING_TYPE_FIXED:
							  $paymentfeeAmount = $paymentfeeFixed;
							  break;
						  case AbstractCarrier::HANDLING_TYPE_PERCENT:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2);
							  break;
						  case HandlingTypes::HANDLING_TYPE_COMBINED:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2)+$paymentfeeFixed;
							  break;
						  case HandlingTypes::HANDLING_TYPE_MIN:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate / 100, 2);
							  if ($paymentfeeAmount < $paymentfeeFixed) {
								  $paymentfeeAmount = $paymentfeeFixed;
							  }
							  break;                            
                      }
					  //Calculate paymentfee
					  $this->_basePaymentfeeAmount += $paymentfeeAmount;
					  $this->addPaymentfeeDescription($paymentfeeDesc,$paymentfeeAmount);
                    }
                  }
              }
		}
		catch (\Exception $e)
		{
			$this->_logLoggerInterface->error($e);
            $this->_helperData->debug('Error in Magecomp Paymentfee Payment Method: '. $e->getMessage());
		}
	}
	
	public function AddPaymentPaymentfee3(Address $address, $quote)
	{
		try
		{
			$storeId = $quote->getStoreId();
			$paymentfeeOn = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeefor/paymentfeeapply', ScopeInterface::SCOPE_STORE, $storeId));
			$applyGroupFilter = $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay3/payfilterenable', ScopeInterface::SCOPE_STORE, $storeId);
            if($applyGroupFilter) 
			{
               $groupFilter = explode(',', $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay3/paygroup', ScopeInterface::SCOPE_STORE, $storeId));
               if(in_array($quote->getCustomerGroupId(), $groupFilter)) 
			   {
                   $apply = true;
               } else {
                   $apply = false;
               }
            } 
			else 
			{
               $apply = true;
            }
			
            if($apply) 
			{
               $data = $quote->getPayment()->getMethod();
			   $paymentMethods = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay3/paymethods', ScopeInterface::SCOPE_STORE, $storeId));
               if(!empty($data)) 
			   {
                  if(in_array($data,$paymentMethods)) 
				  {
					  //add paymentfee
					  //choose between fixed/percent handling types
                      $handlingType =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay3/payfeetype', ScopeInterface::SCOPE_STORE, $storeId);
                      $subtotal = 0;
					  
					  foreach ($paymentfeeOn as $paymentfeeComponent) 
					  {
						  switch ($paymentfeeComponent) 
						  {
							  case Totals::SURCHARGE_ON_SUBTOTAL:
								  $subtotal += $address->getBaseSubtotal();
								  break;
							  case Totals::SURCHARGE_ON_SHIPPING:
								  $subtotal += $address->getBaseShippingAmount();
								  break;
							  case Totals::SURCHARGE_ON_TAX:
								  $subtotal +=$address->getBaseTaxAmount();
								  break;
							  case Totals::SURCHARGE_EXCLUDE_DISCOUNT:
								  $subtotal +=$address->getBaseDiscountAmount();
								  break;
						  }
					  }

					  $paymentfeeFixed =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay3/payratefix', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeRate =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay3/payrateper', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeDesc =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay3/paydesc', ScopeInterface::SCOPE_STORE, $storeId);
                            
					  switch($handlingType)
					  {
						  case AbstractCarrier::HANDLING_TYPE_FIXED:
							  $paymentfeeAmount = $paymentfeeFixed;
							  break;
						  case AbstractCarrier::HANDLING_TYPE_PERCENT:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2);
							  break;
						  case HandlingTypes::HANDLING_TYPE_COMBINED:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2)+$paymentfeeFixed;
							  break;
						  case HandlingTypes::HANDLING_TYPE_MIN:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate / 100, 2);
							  if ($paymentfeeAmount < $paymentfeeFixed) {
								  $paymentfeeAmount = $paymentfeeFixed;
							  }
							  break;                            
                      }
					  //Calculate paymentfee
					  $this->_basePaymentfeeAmount += $paymentfeeAmount;
					  $this->addPaymentfeeDescription($paymentfeeDesc,$paymentfeeAmount);
                    }
                  }
              }
		}
		catch (\Exception $e)
		{
			$this->_logLoggerInterface->error($e);
            $this->_helperData->debug('Error in Magecomp Paymentfee Payment Method: '. $e->getMessage());
		}
	}
	
	public function AddPaymentPaymentfee4(Address $address, $quote)
	{
		try
		{
			$storeId = $quote->getStoreId();
			$paymentfeeOn = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeefor/paymentfeeapply', ScopeInterface::SCOPE_STORE, $storeId));
			$applyGroupFilter = $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay4/payfilterenable', ScopeInterface::SCOPE_STORE, $storeId);
            if($applyGroupFilter) 
			{
               $groupFilter = explode(',', $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay4/paygroup', ScopeInterface::SCOPE_STORE, $storeId));
               if(in_array($quote->getCustomerGroupId(), $groupFilter)) 
			   {
                   $apply = true;
               } else {
                   $apply = false;
               }
            } 
			else 
			{
               $apply = true;
            }
			
            if($apply) 
			{
               $data = $quote->getPayment()->getMethod();
			   $paymentMethods = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay4/paymethods', ScopeInterface::SCOPE_STORE, $storeId));
               if(!empty($data)) 
			   {
                  if(in_array($data,$paymentMethods)) 
				  {
					  //add paymentfee
					  //choose between fixed/percent handling types
                      $handlingType =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay4/payfeetype', ScopeInterface::SCOPE_STORE, $storeId);
                      $subtotal = 0;
					  
					  foreach ($paymentfeeOn as $paymentfeeComponent) 
					  {
						  switch ($paymentfeeComponent) 
						  {
							  case Totals::SURCHARGE_ON_SUBTOTAL:
								  $subtotal += $address->getBaseSubtotal();
								  break;
							  case Totals::SURCHARGE_ON_SHIPPING:
								  $subtotal += $address->getBaseShippingAmount();
								  break;
							  case Totals::SURCHARGE_ON_TAX:
								  $subtotal +=$address->getBaseTaxAmount();
								  break;
							  case Totals::SURCHARGE_EXCLUDE_DISCOUNT:
								  $subtotal +=$address->getBaseDiscountAmount();
								  break;
						  }
					  }

					  $paymentfeeFixed =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay4/payratefix', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeRate =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay4/payrateper', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeDesc =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay4/paydesc', ScopeInterface::SCOPE_STORE, $storeId);
                            
					  switch($handlingType)
					  {
						  case AbstractCarrier::HANDLING_TYPE_FIXED:
							  $paymentfeeAmount = $paymentfeeFixed;
							  break;
						  case AbstractCarrier::HANDLING_TYPE_PERCENT:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2);
							  break;
						  case HandlingTypes::HANDLING_TYPE_COMBINED:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2)+$paymentfeeFixed;
							  break;
						  case HandlingTypes::HANDLING_TYPE_MIN:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate / 100, 2);
							  if ($paymentfeeAmount < $paymentfeeFixed) {
								  $paymentfeeAmount = $paymentfeeFixed;
							  }
							  break;                            
                      }
					  //Calculate paymentfee
					  $this->_basePaymentfeeAmount += $paymentfeeAmount;
					  $this->addPaymentfeeDescription($paymentfeeDesc,$paymentfeeAmount);
                    }
                  }
              }
		}
		catch (\Exception $e)
		{
			$this->_logLoggerInterface->error($e);
            $this->_helperData->debug('Error in Magecomp Paymentfee Payment Method: '. $e->getMessage());
		}
	}
	
	public function AddPaymentPaymentfee5(Address $address, $quote)
	{
		try
		{
			$storeId = $quote->getStoreId();
			$paymentfeeOn = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeefor/paymentfeeapply', ScopeInterface::SCOPE_STORE, $storeId));
			$applyGroupFilter = $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay5/payfilterenable', ScopeInterface::SCOPE_STORE, $storeId);
            if($applyGroupFilter) 
			{
               $groupFilter = explode(',', $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay5/paygroup', ScopeInterface::SCOPE_STORE, $storeId));
               if(in_array($quote->getCustomerGroupId(), $groupFilter)) 
			   {
                   $apply = true;
               } else {
                   $apply = false;
               }
            } 
			else 
			{
               $apply = true;
            }
			
            if($apply) 
			{
               $data = $quote->getPayment()->getMethod();
			   $paymentMethods = explode(',',$this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay5/paymethods', ScopeInterface::SCOPE_STORE, $storeId));
               if(!empty($data)) 
			   {
                  if(in_array($data,$paymentMethods)) 
				  {
					  //add paymentfee
					  //choose between fixed/percent handling types
                      $handlingType =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay5/payfeetype', ScopeInterface::SCOPE_STORE, $storeId);
                      $subtotal = 0;
					  
					  foreach ($paymentfeeOn as $paymentfeeComponent) 
					  {
						  switch ($paymentfeeComponent) 
						  {
							  case Totals::SURCHARGE_ON_SUBTOTAL:
								  $subtotal += $address->getBaseSubtotal();
								  break;
							  case Totals::SURCHARGE_ON_SHIPPING:
								  $subtotal += $address->getBaseShippingAmount();
								  break;
							  case Totals::SURCHARGE_ON_TAX:
								  $subtotal +=$address->getBaseTaxAmount();
								  break;
							  case Totals::SURCHARGE_EXCLUDE_DISCOUNT:
								  $subtotal +=$address->getBaseDiscountAmount();
								  break;
						  }
					  }

					  $paymentfeeFixed =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay5/payratefix', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeRate =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay5/payrateper', ScopeInterface::SCOPE_STORE, $storeId);
					  $paymentfeeDesc =  $this->_configScopeConfigInterface->getValue('paymentfee/paymentfeepay5/paydesc', ScopeInterface::SCOPE_STORE, $storeId);
                            
					  switch($handlingType)
					  {
						  case AbstractCarrier::HANDLING_TYPE_FIXED:
							  $paymentfeeAmount = $paymentfeeFixed;
							  break;
						  case AbstractCarrier::HANDLING_TYPE_PERCENT:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2);
							  break;
						  case HandlingTypes::HANDLING_TYPE_COMBINED:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate/100,2)+$paymentfeeFixed;
							  break;
						  case HandlingTypes::HANDLING_TYPE_MIN:
							  $paymentfeeAmount = round($subtotal * $paymentfeeRate / 100, 2);
							  if ($paymentfeeAmount < $paymentfeeFixed) {
								  $paymentfeeAmount = $paymentfeeFixed;
							  }
							  break;                            
                      }
					  //Calculate paymentfee
					  $this->_basePaymentfeeAmount += $paymentfeeAmount;
					  $this->addPaymentfeeDescription($paymentfeeDesc,$paymentfeeAmount);
                    }
                  }
              }
		}
		catch (\Exception $e)
		{
			$this->_logLoggerInterface->error($e);
            $this->_helperData->debug('Error in Magecomp Paymentfee Payment Method: '. $e->getMessage());
		}
	}
}
