<?php
namespace Ktpl\AddonPopup\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory; 
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
 
class UpgradeData implements UpgradeDataInterface {
 
    public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;
 
        if ( version_compare( $context->getVersion(), '1.0.1', '<' ) ) {
            if ( $installer->getTableRow( $installer->getTable( 'blog_posts' ), 'post_id', 1 ) ) {
                $installer->updateTableRow(
                    $installer->getTable( 'blog_posts' ),
                    'post_id',
                    1,
                    'title',
                    'Welcome to Your Magento 2 Blog!'
                );
            }
        }
    }
}