<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Jet
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Jet\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Jet module DataBase scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.3', '<=')) {

            $tName = $setup->getTable('jet_category_list');

            $setup->getConnection()->truncateTable($tName);

            if ($setup->getConnection()->isTableExists($tName) == true) {

                $setup->getConnection()->addColumn($tName,
                    'is_taxable_product',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'unsigned' => true,
                        'comment' => 'Is Taxable Product'
                    ]
                );
                $setup->getConnection()->addColumn($tName,
                    'attribute_ids',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'unsigned' => true,
                        'comment' => 'Attribute Ids'
                    ]
                );

                $setup->getConnection()->dropColumn($tName, 'path');
                $setup->getConnection()->dropColumn($tName, 'attribute_id');
            }
            $tName = $setup->getTable('jet_order_import_error');
            if ($setup->getConnection()->isTableExists($tName) == true) {

                $setup->getConnection()->addColumn($tName,
                    'status',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'unsigned' => true,
                        'comment' => 'Status'
                    ]
                );
                $setup->getConnection()->addColumn($tName,
                    'order_time',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'unsigned' => true,
                        'comment' => 'Order Time'
                    ]
                );
            }

            $tName = $setup->getTable('jet_attribute_mapping');
            if ($setup->getConnection()->isTableExists($tName) == true) {

                $setup->getConnection()->addColumn($tName,
                    'magento_attribute_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'unsigned' => true,
                        'comment' => 'Magento Attribute ID',
                        'default' => 0
                    ]
                );
            }

            $tName = $setup->getTable('jet_return_table');
            if ($setup->getConnection()->isTableExists($tName) == true) {
                $setup->getConnection()->addColumn($tName,
                    'reference_order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                        'nullable' => false,
                        'unsigned' => true,
                        'comment' => 'Reference Order Id'
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
