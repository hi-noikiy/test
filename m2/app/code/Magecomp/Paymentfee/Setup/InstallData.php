<?php
/**
 * Magento Paymentfee extension
 *
 * @category   Magecomp
 * @package    Magecomp_Paymentfee
 * @author     Magecomp
 */
namespace Magecomp\Paymentfee\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Sales\Setup\SalesSetupFactory;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;
	
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
	
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		
		$setup->startSetup();
		$salesInstaller = $this->eavSetupFactory->create(['setup' => $setup]);
		
		$salesInstaller->addAttribute('order', 
			'mc_paymentfee_amount', 
			[
				'type' => 'decimal',
				'visible'      => true,
    			'visible_on_front'      => true,
    			'required'     => false,
    			'user_defined' => false,
				'label'  => 'Paymentfee Amount',
    			'backend_type'    => 'decimal'
			]
		);
		
		$salesInstaller->addAttribute('order', 
			'base_mc_paymentfee_amount', 
			[
				'type' => 'decimal',
				'visible'      => true,
    			'visible_on_front'      => true,
    			'required'     => false,
    			'user_defined' => false,
				'label'  => 'Base Paymentfee Amount',
    			'backend_type'    => 'decimal'
			]
		);
		
		$salesInstaller->addAttribute('order', 
			'mc_paymentfee_description', 
			[
				'visible'      => true,
    			'visible_on_front'      => true,
    			'required'     => false,
    			'user_defined' => false,
				'label'  => 'Paymentfee Description',
    			'backend_type'    => 'varchar',
				'type' => 'varchar'
			]
		);
		
        $setup->endSetup();
    }
}