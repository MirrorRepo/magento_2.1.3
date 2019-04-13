<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Ebay
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\Ebay\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */

class InstallSchema implements InstallSchemaInterface
{
    /**
     *
     * {@inheritdoc} @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'ebay_profile'
         */
        $table = $installer->getConnection()->newTable( $installer->getTable( 'ebay_profile' ) )
            ->addColumn('id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'ID'
            )->addColumn('profile_code',
                        Table::TYPE_TEXT,
                        50,
                        ['nullable' => false, 'default' => ''],
                        'Profile Code'
            )
            ->addColumn('profile_status',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => true, 'default' => 1],
                        'Profile Status'
            )
            ->addColumn('profile_name',
                        Table::TYPE_TEXT,
                        50,
                        ['nullable' => false, 'default' => ''],
                        'Profile Name'
            )
            ->addColumn('profile_category',
                        Table::TYPE_TEXT,
                        500,
                        ['nullable' => true, 'default' => ''],
                        'Profile Category'
            )
            ->addColumn('profile_cat_attribute',
                        Table::TYPE_TEXT,
                        500,
                        ['nullable' => true, 'default' => ''],
                        'Profile Category Attribute'
            )
            ->addColumn('profile_req_opt_attribute',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => true, 'default' => ''],
                        'Profile Required And Optional Attribute'
            )
            ->addColumn( 'profile_cat_feature', 
                        Table::TYPE_TEXT, 
                        50,
                        ['nullable' => true, 'default' => ''],
                        'Profile Category Feature'
            )->addIndex(
                $setup->getIdxName(
                    'ebay_profile',
                    ['profile_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['profile_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Profile Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

        $installer->getConnection()->createTable( $table );

        /**
         * Create table 'ebay_profile'
         */

         $table = $installer->getConnection()->newTable( $installer->getTable( 'ebay_profile_products' ) )
            ->addColumn('id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'ID'
            )->addColumn('profile_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Profile Id'
            )->addColumn('product_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Product ID'
            )->addColumn('ebay_item_id',
                        Table::TYPE_BIGINT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'eBay Item ID'
            )
            ->addForeignKey(
                $setup->getFkName('ebay_profile_products', 'profile_id', 'ebay_profile', 'id'),
                'profile_id',
                $setup->getTable('ebay_profile'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addIndex(
                $setup->getIdxName(
                    'ebay_profile_products',
                    ['profile_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['profile_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex( $setup->getIdxName(
                    'ebay_profile_products',
                    ['product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Profile Products Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

        $installer->getConnection()->createTable( $table );

        /**
         * Create table 'ebay_orders'
         */
        $table = $installer->getConnection()->newTable( $installer->getTable( 'ebay_orders' ) )
            ->addColumn('id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'ID'
            )
            ->addColumn('ebay_order_id',
                        Table::TYPE_TEXT,
                        50,
                        ['nullable' => false, 'default' => ''],
                        'eBay Order Id'
            )
            ->addColumn('magento_order_id',
                        Table::TYPE_TEXT,
                        50,
                        ['nullable' => false, 'default' => ''],
                        'Magento Order Id'
            )
            ->addColumn('order_place_date',
                        Table::TYPE_DATE,
                        null,
                        ['nullable' => false],
                        'Order Place Date'
            )
            ->addColumn('status',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'eBay Order Status'
            )
            ->addColumn('order_data',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => true, 'default' => ''],
                        'Order Data'
            )
            ->addColumn( 'shipment_data', 
                        Table::TYPE_TEXT, 
                        null,
                        ['nullable' => true, 'default' => ''],
                        'Order Shipment Data'
            )->setComment('eBay Orders')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

        $installer->getConnection()->createTable( $table );
    }
}
