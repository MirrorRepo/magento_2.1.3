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
namespace Ced\Jet\Block\Adminhtml\Categories;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('grid_record');
    }

    /**
     * @return $this
     */
    public function _prepareCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $collection = $objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter('id', $id);

        } else {
            $collection = $objectManager->create('Ced\Jet\Model\Categories')->getCollection();
        }
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('Id'),
            'type' => 'int',
            'index' => 'id'
        ]);
        $this->addColumn('cat_id', [
            'header' => __('Jet Category Id'),
            'type' => 'int',
            'index' => 'cat_id'
        ]);
        $this->addColumn('name', [
            'header' => __('Jet Category Name'),
            'index' => 'name',
            'type' => 'text'
        ]);
        $this->addColumn('parent_cat_id', [
            'header' => __('Jet Parent Category Id'),
            'index' => 'parent_cat_id',
            'type' => 'text'
        ]);
        $this->addColumn('jetattr_name', [
            'header' => __('Jet Attributes Names'),
            'index' => 'jetattr_names',
            'type' => 'text'
        ]);
        $this->addColumn('level', [
            'header' => __('level'),
            'index' => 'level',
            'type' => 'text'
        ]);
        $this->addColumn('is_taxable_product', [
            'header' => __('Is Taxable Product'),
            'index' => 'is_taxable_product',
            'type' => 'text'
        ]);
        $this->addColumn('magento_cat_id', [
            'header' => __('Magento Category id'),
            'index' => 'magento_cat_id',
            'type' => 'int'
        ]);
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->addColumn(
                'Edit',
                [
                    'header' => __('View Attribute Details'),
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => [
                        [
                            'caption' => __('View Details'),
                            'url' => [
                                'base' => '*/*/view'
                            ],
                            'field' => 'id'
                        ]
                    ],
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'header_css_class' => 'col-action',
                    'column_css_class' => 'col-action'
                ]
            );
        }


        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('jet/*/grid', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'jet/*/view',
            ['id' => $row->getId()]
        );
    }
}
