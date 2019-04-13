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
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Jet\Plugin\Product\Form\Modifier\CustomOptions;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;

class ModifyMeta
{
    const FORM_NAME = 'product_form';
    const DATA_SOURCE_DEFAULT = 'product';
    const DATA_SCOPE_PRODUCT = 'data.product';

    /**
     * Name of default general panel
     */
    const DEFAULT_GENERAL_PANEL = 'product-details';

    /**
     * Default general panel order
     */
    const GENERAL_PANEL_ORDER = 10;

    /**
     * Container fieldset prefix
     */
    const CONTAINER_PREFIX = 'container_';

    /**
     * Meta config path
     */
    const META_CONFIG_PATH = '/arguments/data/config';
    /**#@+
     * Group values
     */
    const GROUP_CUSTOM_OPTIONS_SCOPE = 'data.product';
    const GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME = 'search-engine-optimization';
    const GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER = 31;
    const GROUP_CUSTOM_OPTIONS_NAME = 'custom_options';
    /**#@-*/

    /**#@+
     * Button values
     */
    const BUTTON_IMPORT = 'button_import';
    const BUTTON_ADD = 'button_add';
    /**#@-*/

    /**#@+
     * Container values
     */
    const CONTAINER_HEADER_NAME = 'container_header';
    const CONTAINER_OPTION = 'container_option';
    const CONTAINER_TYPE_STATIC_NAME = 'container_type_static';
    const CONTAINER_COMMON_NAME = 'container_common';
    /**#@-*/

    /**#@+
     * Grid values
     */
    const GRID_TYPE_SELECT_NAME = 'values';
    const GRID_OPTIONS_NAME = 'options';
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_ENABLE = 'affect_product_custom_options';
    const FIELD_TITLE_NAME = 'title';
    const FIELD_TYPE_NAME = 'type';
    const FIELD_IS_REQUIRE_NAME = 'is_require';
    const FIELD_SORT_ORDER_NAME = 'sort_order';
    const FIELD_PRICE_TYPE_NAME = 'price_type';
    const FIELD_SKU_NAME = 'sku';
    const FIELD_OPTION_ID = 'option_id';
    const FIELD_MAX_CHARACTERS_NAME = 'max_characters';
    const FIELD_FILE_EXTENSION_NAME = 'file_extension';
    const FIELD_PRICE_NAME = 'price';
    const FIELD_IMAGE_SIZE_X_NAME = 'image_size_x';
    const FIELD_IS_DELETE = 'is_delete';
    const FIELD_IMAGE_SIZE_Y_NAME = 'image_size_y';
    /**#@-*/

    /**#@+
     * Import options values
     */
    const IMPORT_OPTIONS_MODAL = 'import_options_modal';
    const CUSTOM_OPTIONS_LISTING = 'product_custom_options_listing';
    /**#@-*/
    public $locator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    public $productOptionsConfig;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Price
     */
    public $productOptionsPrice;

    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * @var ArrayManager
     */
    public $arrayManager;

    /**
     * @var array
     */
    public $meta = [];

    /**
     * @var CurrencyInterface
     */
    public $localeCurrency;

    /**
     * ModifyMeta constructor.
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $productOptionsConfig
     * @param ProductOptionsPrice $productOptionsPrice
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ConfigInterface $productOptionsConfig,
        ProductOptionsPrice $productOptionsPrice,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager
    ) {

        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->productOptionsConfig = $productOptionsConfig;
        $this->productOptionsPrice = $productOptionsPrice;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
    }

    /**
     *  Add something to the product custom options component
     *
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options $subject
     * @param string $result
     * @return string
     */
    public function afterModifyMeta($subject, $result)
    {
        $gcoName = \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions::GROUP_CUSTOM_OPTIONS_NAME; // custom_options container

        if (!empty($result[$gcoName])) {
            $product = $this->locator->getProduct(); // current product
            $productId  = $product->getId();

            $result[$gcoName]['children'] = array_replace_recursive(
                $result[$gcoName]['children'],
                [
                    'custom_options' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('My Ui Form'),
                                    'componentType' => Fieldset::NAME,
                                    'dataScope' => 'data.product',
                                    'collapsible' => true,
                                    'sortOrder' => "1000"
                                    // config data here @see \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions for examples
                                ],
                            ],
                        ],
                        'children' => [
                            static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                            static::FIELD_ENABLE => $this->getEnableFieldConfig(20),
                            static::GRID_OPTIONS_NAME => $this->getOptionsGridConfig(30)
                        ]
                    ]
                ]
            );
        }

        return $result;
    }

    /**
     * @param $orderSort
     * @return array
     */
    public function getHeaderContainerConfig($orderSort)
    {
        return [
            'children' => [
                static::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add Option'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => 'ns = ${ $.ns }, index = ' . static::GRID_OPTIONS_NAME,
                                        'actionName' => 'processingAddChild',
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
                static::BUTTON_IMPORT => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Import Options'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => 'ns=' . static::CUSTOM_OPTIONS_LISTING
                                            . ', index=' . static::CUSTOM_OPTIONS_LISTING,
                                        'actionName' => 'render',
                                    ],
                                    [
                                        'targetName' => 'ns=' . static::FORM_NAME . ', index='
                                            . static::IMPORT_OPTIONS_MODAL,
                                        'actionName' => 'openModal',
                                    ],
                                    [
                                        'targetName' => 'ns=' . static::FORM_NAME . ', index=options',
                                        'actionName' => 'clearDataProvider'
                                    ],
                                ],
                                'displayAsLink' => true,
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $orderSort,
                        'content' => __('Custom options let customers choose the product variations they want.'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for the whole grid
     *
     * @param int $orderSort
     * @return array
     */
    public function getOptionsGridConfig($orderSort)
    {
        return [
            'children' => [
                'record' => [
                    'children' => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(40),
                                static::CONTAINER_COMMON_NAME => $this->getCommonContainerConfig(10),
                                static::CONTAINER_TYPE_STATIC_NAME => $this->getStaticTypeContainerConfig(20),
                                static::GRID_TYPE_SELECT_NAME => $this->getSelectTypeGridConfig(30)
                            ]
                        ],
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('New Option'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                ]
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Option'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Catalog/js/components/dynamic-rows-import-custom-options',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $orderSort,
                        'dataProvider' => static::CUSTOM_OPTIONS_LISTING,
                        'links' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field responsible for enabling custom options processing
     *
     * @param int $orderSort
     * @return array
     */
    public function getEnableFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_ENABLE,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $orderSort,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for modal window "Import Options"
     *
     * @return array
     */
    public function getImportOptionsModalConfig()
    {
        return [
            'children' => [
                static::CUSTOM_OPTIONS_LISTING => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'insertListing',
                                'externalProvider' => static::CUSTOM_OPTIONS_LISTING . '.'
                                    . static::CUSTOM_OPTIONS_LISTING . '_data_source',
                                'selectionsProvider' => static::CUSTOM_OPTIONS_LISTING . '.'
                                    . static::CUSTOM_OPTIONS_LISTING . '.product_columns.ids',
                                'ns' => static::CUSTOM_OPTIONS_LISTING,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'dataScope' => static::CUSTOM_OPTIONS_LISTING,
                                'behaviourType' => 'edit',
                                'externalFilterMode' => true,
                                'dataLinks' => [
                                    'imports' => false,
                                    'exports' => true
                                ],
                                'currentProductId' => $this->locator->getProduct()->getId(),
                                'exports' => [
                                    'currentProductId' => '${ $.externalProvider }:params.current_product_id'
                                ]
                            ],
                        ],
                    ],
                ],
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope' => '',
                        'provider' => static::FORM_NAME . '.product_form_data_source',
                        'options' => [
                            'title' => __('Select Product'),
                            'buttons' => [
                                [
                                    'text' => __('Import'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = ' . static::CUSTOM_OPTIONS_LISTING,
                                            'actionName' => 'save'
                                        ],
                                        'closeModal'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for container with common fields for any type
     *
     * @param int $orderSort
     * @return array
     */
    public function getCommonContainerConfig($orderSort)
    {
        $commonContainer = [
            'children' => [
                static::FIELD_OPTION_ID => $this->getOptionIdFieldConfig(10),
                static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(
                    20,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Option Title'),
                                    'component' => 'Magento_Catalog/component/static-type-input',
                                    'valueUpdate' => 'input',
                                    'imports' => [
                                        'optionId' => '${ $.provider }:${ $.parentScope }.option_id'
                                    ]
                                ],
                            ],
                        ],
                    ]
                ),
                static::FIELD_TYPE_NAME => $this->getTypeFieldConfig(30),
                static::FIELD_IS_REQUIRE_NAME => $this->getIsRequireFieldConfig(40)
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $orderSort,
                    ],
                ],
            ]
        ];

        if ($this->locator->getProduct()->getStoreId()) {
            $useDefaultConfig = [
                'service' => [
                    'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
                ]
            ];
            $titlePath = $this->arrayManager->findPath(static::FIELD_TITLE_NAME, $commonContainer, null)
                . static::META_CONFIG_PATH;
            $commonContainer = $this->arrayManager->merge($titlePath, $commonContainer, $useDefaultConfig);
        }

        return $commonContainer;
    }

    /**
     * Get config for container with fields for all types except "select"
     *
     * @param int $orderSort
     * @return array
     */
    public function getStaticTypeContainerConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $orderSort,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_PRICE_NAME => $this->getPriceFieldConfig(10),
                static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(20),
                static::FIELD_SKU_NAME => $this->getSkuFieldConfig(30),
                static::FIELD_MAX_CHARACTERS_NAME => $this->getMaxCharactersFieldConfig(40),
                static::FIELD_FILE_EXTENSION_NAME => $this->getFileExtensionFieldConfig(50),
                static::FIELD_IMAGE_SIZE_X_NAME => $this->getImageSizeXFieldConfig(60),
                static::FIELD_IMAGE_SIZE_Y_NAME => $this->getImageSizeYFieldConfig(70)
            ]
        ];
    }

    /**
     * Get config for grid for "select" types
     *
     * @param int $orderSort
     * @return array
     */
    public function getSelectTypeGridConfig($orderSort)
    {
        return [
            'children' => [
                'record' => [
                    'children' => [
                        static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(10),
                        static::FIELD_PRICE_NAME => $this->getPriceFieldConfig(20),
                        static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(30, ['fit' => true]),
                        static::FIELD_SKU_NAME => $this->getSkuFieldConfig(40),
                        static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(50),
                        static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(60)
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                ]
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Value'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $orderSort,
                    ],
                ],
            ]
        ];
    }

    /**
     * Get config for hidden id field
     *
     * @param int $orderSort
     * @return array
     */
    public function getOptionIdFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_OPTION_ID,
                        'sortOrder' => $orderSort,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Title" fields
     *
     * @param int $orderSort
     * @param array $options
     * @return array
     */
    public function getTitleFieldConfig($orderSort, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Title'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => static::FIELD_TITLE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $orderSort,
                            'validation' => [
                                'required-entry' => true
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    /**
     * Get config for "Option Type" field
     *
     * @param int $orderSort
     * @return array
     */
    public function getTypeFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Option Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'component' => 'Magento_Catalog/js/custom-options-type',
                        'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                        'selectType' => 'optgroup',
                        'dataScope' => static::FIELD_TYPE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $orderSort,
                        'options' => $this->getProductOptionTypes(),
                        'disableLabel' => true,
                        'selectedPlaceholders' => [
                            'defaultPlaceholder' => __('-- Please select --'),
                        ],
                        'multiple' => false,
                        'validation' => [
                            'required-entry' => true
                        ],
                        'groupsConfig' => [
                            'text' => [
                                'values' => ['field', 'area'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME,
                                    static::FIELD_MAX_CHARACTERS_NAME
                                ]
                            ],
                            'select' => [
                                'values' => ['drop_down', 'radio', 'checkbox', 'multiple'],
                                'indexes' => [
                                    static::GRID_TYPE_SELECT_NAME
                                ]
                            ],
                            'file' => [
                                'values' => ['file'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME,
                                    static::FIELD_FILE_EXTENSION_NAME,
                                    static::FIELD_IMAGE_SIZE_X_NAME,
                                    static::FIELD_IMAGE_SIZE_Y_NAME
                                ]
                            ],
                            'data' => [
                                'values' => ['date', 'date_time', 'time'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Required" field
     *
     * @param int $orderSort
     * @return array
     */
    public function getIsRequireFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Required'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => static::FIELD_IS_REQUIRE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $orderSort,
                        'value' => '1',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for sorting
     *
     * @param int $orderSort
     * @return array
     */
    public function getPositionFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $orderSort,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for removing rows
     *
     * @param int $orderSort
     * @return array
     */
    public function getIsDeleteFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit' => true,
                        'sortOrder' => $orderSort
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Price" field
     *
     * @param int $orderSort
     * @return array
     */
    public function getPriceFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Price'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_NAME,
                        'dataType' => Number::NAME,
                        'addbefore' => $this->getCurrencySymbol(),
                        'sortOrder' => $orderSort,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Price Type" field
     *
     * @param int $orderSort
     * @param array $config
     * @return array
     */
    public function getPriceTypeFieldConfig($orderSort, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Price Type'),
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'dataScope' => static::FIELD_PRICE_TYPE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $orderSort,
                            'options' => $this->productOptionsPrice->toOptionArray(),
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    /**
     * Get config for "SKU" field
     *
     * @param int $orderSort
     * @return array
     */
    public function getSkuFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('SKU'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SKU_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $orderSort,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Max Characters" field
     *
     * @param int $orderSort
     * @return array
     */
    public function getMaxCharactersFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Max Characters'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_MAX_CHARACTERS_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $orderSort,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "File Extension" field
     *
     * @param int $orderSort
     * @return array
     */
    public function getFileExtensionFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Compatible File Extensions'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_FILE_EXTENSION_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $orderSort,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Maximum Image Width" field
     *
     * @param int $orderSort
     * @return array
     */
    public function getImageSizeXFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Maximum Image Size'),
                        'notice' => __('Please leave blank if it is not an image.'),
                        'addafter' => __('px.'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_IMAGE_SIZE_X_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $orderSort,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Maximum Image Height" field
     *
     * @param int $orderSort
     * @return array
     */
    public function getImageSizeYFieldConfig($orderSort)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => ' ',
                        'addafter' => __('px.'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_IMAGE_SIZE_Y_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $orderSort,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get options for drop-down control with product option types
     *
     * @return array
     */
    public function getProductOptionTypes()
    {
        $options = [];
        $groupIndex = 0;

        foreach ($this->productOptionsConfig->getAll() as $option) {
            $group = [
                'value' => $groupIndex,
                'label' => $option['label'],
                'optgroup' => []
            ];

            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }
                $group['optgroup'][] = ['label' => $type['label'], 'value' => $type['name']];
            }

            if (!empty($group['optgroup'])) {
                $options[] = $group;
                $groupIndex += 1;
            }
        }

        return $options;
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * The getter function to get the locale currency for real application code
     *
     * @return \Magento\Framework\Locale\CurrencyInterface
     *
     * @deprecated
     */
    public function getLocaleCurrency()
    {
        if ($this->localeCurrency === null) {
            $this->localeCurrency = \Magento\Framework\App\ObjectManager::getInstance()->get(CurrencyInterface::class);
        }
        return $this->localeCurrency;
    }

    /**
     * Format price according to the locale of the currency
     *
     * @param mixed $value
     * @return string
     */
    public function formatPrice($value)
    {
        if (!is_numeric($value)) {
            return null;
        }

        $store = $this->storeManager->getStore();
        $currency = $this->getLocaleCurrency()->getCurrency($store->getBaseCurrencyCode());
        $value = $currency->toCurrency($value, ['display' => \Magento\Framework\Currency::NO_SYMBOL]);

        return $value;
    }
}
