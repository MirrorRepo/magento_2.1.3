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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'jet_category_list'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_category_list'))
                ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [ 
                                'identity' => true,
                                'unsigned' => true,
                                'nullable' => false,
                                'primary' => true 
                                ], 
                            'ID' 
                   )
                ->addColumn('cat_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [ 
                                'unsigned' => true,
                                'nullable' => false,
                                'default' => '0' 
                                ],
                            'Cat Id' 
                   )
                ->addColumn('parent_cat_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [ 
                                'nullable' => false 
                                ], 
                            'Parent Cat Id' 
                   )
                ->addColumn('name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [ 
                                'unsigned' => true,
                                'nullable' => false,
                                ],
                            'Name' 
                   )
                ->addColumn('path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [ 
                                'unsigned' => true,
                                'nullable' => false,
                                ],
                            'path' 
                   )
                ->addColumn('level', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [ 
                                'unsigned' => true,
                                'default' => '0' 
                                ],
                            'Level' 
                   )
                ->addColumn('attribute_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [ 
                                'unsigned' => true,
                                'default' => '0' 
                                ], 
                            'Attribute Id' 
                   )
                ->addColumn('jetattr_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [ 
                                'unsigned' => true,
                                'nullable' => true,
                                ],
                            'Jet Attributes' 
                   )
                ->addColumn('magento_cat_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [ 
                                'unsigned' => true,
                                'default' => '0' 
                                ],
                             'Magento Category Id' 
                   );
        
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'jet_order_import_error'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_order_import_error'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('merchant_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Merchant Order Id'
            )
            ->addColumn('reference_number', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => '0'
            ],
                'Reference Number'
            )
            ->addColumn('reason', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Reason'
            )
            ->addColumn('order_item_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Order Item Id'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'jet_attribute_mapping'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_attribute_mapping'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('jet_attribute_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Jet Attribute ID'
            )
            ->addColumn('magento_attribute_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ], 'Magento Attribute Code'
            )
            ->addColumn('free_text', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Free Text'
            )
            ->addColumn('variant', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'default' => '0'
            ],
                'Variant'
            )
            ->addColumn('variant_pair', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'nullable' => false,
                'default' => '0'
            ],
                'Variant Pair'
            )
            ->addColumn('unit', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => false,
            ],
                'Unit'
            )
            ->addColumn('list_option', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'List Option'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'jet_batch_upload_errors'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_batch_upload_errors'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Product Id'
            )
            ->addColumn('product_sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Product Sku'
            )
            ->addColumn('batch_num', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'nullable' => false,
                'default' => '0'
            ],
                'Batch Number'
            )
            ->addColumn('is_write_mode', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'default' => '0'
            ],
                'Is Write Mode'
            )
            ->addColumn('error', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => '0'
            ],
                'Error'
            )
            ->addColumn('date_added', \Magento\Framework\DB\Ddl\Table::TYPE_DATE, null, [
                'nullable' => false
            ],
                'Date Added'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'jet_errorfile_info'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_errorfile_info'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('jet_file_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Jet File Id'
            )
            ->addColumn('file_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'File Name'
            )
            ->addColumn('file_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'File Type'
            )
            ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => ''
            ],
                'Status'
            )
            ->addColumn('error', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => '0'
            ],
                'Error'
            )
            ->addColumn('date', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [
                'nullable' => false,
            ],
                'Date'
            )
            ->addColumn('jetinfofile_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, [
                'nullable' => false,
                'default' => '0'
            ],
                'Jetinfofile Id'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'jet_file_info'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_file_info'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('magento_batch_info', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Magento Batch Info'
            )
            ->addColumn('jet_file_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Jet File Id'
            )
            ->addColumn('token_url', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Token Url'
            )
            ->addColumn('file_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => ''
            ],
                'File Name'
            )
            ->addColumn('file_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => ''
            ],
                'File Type'
            )
            ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => ''
            ],
                'Status'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'jet_order_detail'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_order_detail'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )->addIndex('jet_order_detail',
                'id',
                ['id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('merchant_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Merchant Order Id'
            )->addIndex(
                'jet_order_detail', 'merchant_order_id',
                [
                    'merchant_order_id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('deliver_by', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Deliver By'
            )->addIndex(
                'jet_order_detail', 'deliver_by',
                [
                    'deliver_by'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('order_place_date', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Order Place Date'
            )->addIndex(
                'jet_order_detail', 'order_place_date',
                [
                    'order_place_date'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('magento_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Magento Order Id'
            )->addIndex(
                'jet_order_detail',
                'magento_order_id',
                [
                    'magento_order_id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'status'
            )->addIndex(
                'jet_order_detail',
                'status',
                [
                    'status'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('order_data', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Order Data'
            )->addIndex(
                'jet_order_detail',
                'order_data',
                [
                    'order_data'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('shipment_data', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Shipping Data'
            )->addIndex(
                'jet_order_detail',
                'shipment_data',
                [
                    'shipment_data'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('reference_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Reference Order Id'
            )->addIndex(
                'jet_order_detail',
                'reference_order_id',
                [
                    'reference_order_id'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('customer_cancelled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'nullable' => true
            ],
                'Customer Cancelled'
            )->addIndex(
                'jet_order_detail',
                'customer_cancelled',
                [
                    'customer_cancelled'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'jet_refund_table'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_refund_table'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('order_item_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,

            ],
                'Order Item Id'
            )
            ->addColumn('quantity_returned', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,

            ],
                'Quantity Returned'
            )
            ->addColumn('refund_quantity', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,

            ],
                'Refund Quantity'
            )
            ->addColumn('refund_reason', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Reason'
            )
            ->addColumn('refund_feedback', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Feedback'
            )
            ->addColumn('refund_amount', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Amount'
            )
            ->addColumn('refund_tax', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Tax'
            )
            ->addColumn('refund_shippingcost', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Shippingcost'
            )
            ->addColumn('refund_shippingtax', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Shippingtax'
            )
            ->addColumn('refund_orderid', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Order Id'
            )
            ->addColumn('refund_merchantid', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund MercahntId'
            )
            ->addColumn('refund_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Id'
            )
            ->addColumn('refund_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'Refund Status'
            )
            ->addColumn('saved_data', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => false,

            ],
                'Saved Data'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table ' jet_return_table'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('jet_return_table'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('merchant_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => false
            ],
                'Merchant Order Id'
            )
            ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => null
            ],
                'Status'
            )
            ->addColumn('returnid', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => null
            ],
                'Return ID'
            )
            ->addColumn('return_details', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => false,
                'default' => false
            ],
                'Return Details'
            )
            ->addColumn('details_saved_after', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => false,
                'default' => false
            ],
                'Details Saved After'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table ' jet_shipping_exception'
         */

        $table = $installer->getConnection()->newTable($installer->getTable('jet_shipping_exception'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'SKU'
            )
            ->addColumn('fulfillment_node_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Fulfillment Node Id'
            )
            ->addColumn('shipping_exception', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Shipping Exception'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table ' jet_optional_attribute'
         */
        
        $table = $installer->getConnection()->newTable($installer->getTable('jet_optional_attribute'))
                ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                                'identity' => true,
                                'unsigned' => true,
                                'nullable' => false,
                                'primary' => true
                                ], 
                            'ID'
                        )
                ->addColumn('jet_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                                'unsigned' => true,
                                'nullable' => false,
                                'unique' => true
                                ], 
                            'Jet Attribute Code'
                   )
                 ->addColumn('label', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                                'unsigned' => true,
                                'nullable' => false,
                                'default' => '0'
                                ], 
                            'Code Label'
                   )
                 ->addColumn('frontend_input', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 25, [
                                'unsigned' => true,
                                'nullable' => false,
                                'default' => '0'
                                ], 
                            'Frontend Input'
                   )
                  ->addColumn('backend_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 25, [
                                'unsigned' => true,
                                'nullable' => false,
                                'default' => '0'
                                ], 
                            'Backend Type'
                   )
                ->addColumn('source_model', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                                'unsigned' => true,
                                'nullable' => true,
                                ], 
                            'Source Model'
                   )
                ->addColumn('backend_model', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                                'unsigned' => true,
                                'nullable' => true,
                                ], 
                            'Backend Model'
                   )
                ->addColumn('used', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                                'unsigned' => true,
                                'nullable' => false,
                                'default' => 0
                                ],
                            'Has Been Used'
                   )
                ->addColumn('map_attribute_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                                'nullable' => true,
                                ], 
                            'Mapped Attribute Code'
                   )
                ->addColumn('note', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                                'nullable' => true,
                                ], 
                            'Note'
                   );
       
        $installer->getConnection()->createTable($table);

        $jet_attributes = [];
        $jet_attributes[] = ['jet_code'      => 'bullets',
                            'frontend_input' => 'textarea',
                            'backend_type'   => 'text',
                            'label'          => 'Bullets',
                            'used'           => 1,
                            'map_attribute_code' => 'bullets',
                            'note'           => 'Please enter product feature description.Add each feature inside \'{}\'.Example :- {This is 
                                                first one.}{This is second one.} and so on.Each \'{}\' contains maximum of 500 characters.
                                                Maximum 5 \'{}\' is allowed.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'legal_disclaimer_description',
                            'frontend_input' => 'textarea',
                            'backend_type'   => 'text',
                            'label'          => 'Legal Disclaimer Description',
                            'note'           => 'Add any legal language required to be displayed with the product.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'safety_warning',
                            'frontend_input' => 'textarea',
                            'backend_type'   => 'text',
                            'label'          => 'Safety Warning',
                            'note'           => 'If applicable, use to supply any associated warnings for your product.Maximum of 500 characters.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'number_units_for_ppu',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Number Units For Price Per Unit',
                            'note'           => 'For Price Per Unit calculations, the number of units included in the merchant SKU. The unit of
                                                measure must be specified in order to indicate what is being measured by the unit-count.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'type_of_unit_for_ppu',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Type of unit for price per unit',
                            'note'           => 'The type_of_unit_for_price_per_unit attribute is a label for the number_units_for_price_per_unit. The price per unit can then be constructed by dividing the selling price by the number of units and appending the text \"per unit value.\" For example, for a six-pack of soda, number_units_for_price_per_unit= 6, type_of_unit_for_price_per_unit= can, price per unit = price per can',
                        ];

        $jet_attributes[] = ['jet_code'      => 'shipping_weight_pounds',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Shipping Weight Pounds',
                            'note'           => 'Weight of the merchant SKU when in its shippable configuration.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'package_length_inches',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Package Length Inches',
                            'note'           => 'Length of the merchant SKU when in its shippable configuration.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'package_width_inches',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Package Width Inches',
                            'note'           => 'Width of the merchant SKU when in its shippable configuration.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'package_height_inches',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Package height inches',
                            'note'           => 'Height of the merchant SKU when in its shippable configuration.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'display_length_inches',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Display Length Inches',
                            'note'           => 'Length of the merchant SKU when in its fully assembled/usable condition.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'display_width_inches',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Display width Inches',
                            'note'           => 'Width of the merchant SKU when in its fully assembled/usable condition.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'display_height_inches',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Display Height Inches',
                            'note'           => 'Height of the merchant SKU when in its fully assembled/usable condition.'
                        ];
        
        $jet_attributes[] = ['jet_code'      => 'fulfillment_time',
                            'frontend_input' => 'text',
                            'backend_type'   => 'int',
                            'label'          => 'Fulfillment Time',
                            'note'           => 'Number of business days from receipt of an order for the given merchant SKU until it will be shipped(only populate if it is different than your account default).'
                        ];

        $jet_attributes[] = ['jet_code'      => 'no_return_fee_adjustment', 
                            'frontend_input' => 'price',
                            'backend_type'   => 'text', 
                            'label'          => 'No return fee adjustment'
                        ];

        $jet_attributes[] = ['jet_code'      => 'map_price', 
                            'frontend_input' => 'price',
                            'backend_type'   => 'text', 
                            'label'          => 'Map Price',
                            'note'           => 'Retailer price for the product for which member savings will be applied.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'category_path', 
                            'frontend_input' => 'text',
                            'backend_type'   => 'text', 
                            'label'          => 'Category Path'
                        ];

        $jet_attributes[] = ['jet_code'      => 'amazon_item_type_keyword', 
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Amazon Item Type Keyword',
                            'note'           => 'ItemType allows customers to find your products as they browse to the most specific item types.
                                                Please use the exact selling from Amazon\'s browse tree guides'
                        ];

        $jet_attributes[] = ['jet_code'      => 'country_of_origin',
                            'frontend_input' => 'text',
                            'backend_type'   => 'text',
                            'label'          => 'Manufacturer',
                            'note'           => 'Country Manufacturer of the merchant SKU - 100 characters.'
                        ];

         $jet_attributes[] = ['jet_code'     => 'start_selling_date', 
                            'frontend_input' => 'date',
                            'backend_type'   => 'datetime',
                            'backend_model'  => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
                            'label'          => 'Start Selling Date',
                            'note'           => 'If updating merchant SKU that has quantity = 0 at all FCs, date that the inventory in this message should be available for sale on Jet.com.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'prop_65', 
                            'frontend_input' => 'boolean', 
                            'backend_type'   => 'int',
                            'source_model'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                            'label'          => 'Proposition 65',
                            'note'           => 'You must tell us if your product is subject to Proposition 65 rules and regulations. Proposition 65 requires merchants to provide California consumers with special warnings for products that contain chemicals known to cause cancer, birth defects, or other reproductive harm, if those products expose consumers to such materials above certain threshold levels. Please view this website for more information: http://www.oehha.ca.gov/.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'exclude_from_fee_adjust', 
                            'frontend_input' => 'boolean', 
                            'backend_type'   => 'int', 
                            'source_model'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                            'label'          => 'Exclude from fee adjustments',
                            'note'           => 'This SKU will not be subject to any fee adjustment rules that are set up if this field is Yes.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'ships_alone', 
                            'frontend_input' => 'boolean', 
                            'backend_type'   => 'int',
                            'source_model'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                            'label'          => 'Ships alone',
                            'note'           => 'If this field is Yes, it indicates that this merchant SKU will always ship on its own.A separate merchant_order will always be placed for this merchant_SKU, one consequence of this will be that this merchant_sku will never contriube to any basket size fee adjustments with any other merchant_skus.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'cpsia_cautionary_statements',
                            'frontend_input' => 'multiselect',
                            'backend_type'   => 'text',
                            'source_model'   => 'Ced\Jet\Model\Source\Caution',
                            'backend_model'  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                            'label'          => 'CPSIA Cautionary Statements',
                            'note'           => 'Use this field to indicate if a cautionary statement relating to the choking hazards of children\'s toys and games applies to your product. These cautionary statements are defined in Section 24 of the Federal Hazardous Substances Act and Section 105 of the Consumer Product Safety Improvement Act of 2008.'
                        ];

        $jet_attributes[] = ['jet_code'      => 'map_implementation', 
                            'frontend_input' => 'select',
                            'backend_type'   => 'text', 
                            'source_model'   => 'Ced\Jet\Model\Source\Mapimp',
                            'label'          => 'Map Implementation',
                            'note'           => 'The type of rule that indicates how Jet member savings are allowed to be applied to an items base price. '
                        ];

        $jet_attributes[] = ['jet_code'      => 'product_tax_code',
                            'frontend_input' => 'select',
                            'backend_type'   => 'text',
                            'source_model'   => 'Ced\Jet\Model\Source\Taxcode',
                            'label'          => 'Product Tax Code',
                            'note'           => 'Jet\'s standard code for the tax properties of a given product.'
                        ];

        
        foreach ($jet_attributes as $key => $value) {
            $installer->getConnection()->insertForce(
                $installer->getTable('jet_optional_attribute'),
                [
                    'jet_code'           => $value['jet_code'],
                    'label'              => $value['label'],
                    'frontend_input'     => $value['frontend_input'],
                    'backend_type'       => $value['backend_type'],
                    'source_model'       => isset($value['source_model']) ? $value['source_model'] : '',
                    'backend_model'      => isset($value['backend_model']) ? $value['backend_model'] : '',
                    'used'               => isset($value['used']) ? $value['used'] : 0,
                    'map_attribute_code' => isset($value['map_attribute_code']) ? $value['map_attribute_code']  : '',
                    'note'               => isset($value['note']) ? $value['note']  : ''
                ]
           );
        }
        
    }
}
