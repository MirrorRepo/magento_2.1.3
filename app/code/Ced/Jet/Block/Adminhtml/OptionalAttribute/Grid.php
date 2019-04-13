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
 * @category    Ced
 * @package     Ced_Jet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Jet\Block\Adminhtml\OptionalAttribute;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        array $data = []
    ) {
        $this->_eavAttribute = $eavAttribute;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
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
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    public function _prepareCollection()
    {

        $collection = $this->_objectManager->create('Ced\Jet\Model\OptionalAttribute')->getCollection()->addFieldToFilter('used',['eq'=>1]);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */
    public function _prepareColumns()
    {
        $this->addColumn('id',
            [
                'header'    => __('ID'),
                'align'     =>'right',
                'width'     => '80px',
                'index'     => 'id'
            ]
        );

        $this->addColumn('jet_code',
            [
                'header'    => __('Jet Attribute Code'),
                'align'     =>'left',
                'index'     => 'jet_code'
            ]
        );

        $this->addColumn('label',
            [
                'header'    => __('Label'),
                'align'     =>'left',
                'index'     => 'label'
            ]
        );

        $this->addColumn('map_attribute_code',
            [
                'header'    => __('Mapped Attribute Code'),
                'align'     =>'left',
                'index'     => 'map_attribute_code'
            ]
        );

        $this->addColumn('action',
            [
                'header'    => __('Action'),
                'type'      => 'action',
                'align'     =>'left',
                'index'     => 'attribute_id',
                'renderer' => 'Ced\Jet\Block\Adminhtml\OptionalAttribute\Renderer\EditAction'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {

        return $this->getUrl('jet/jetattribute/grid', ['_current' => true]);
    }
}
