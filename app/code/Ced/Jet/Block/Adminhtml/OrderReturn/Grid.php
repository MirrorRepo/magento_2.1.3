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

namespace Ced\Jet\Block\Adminhtml\OrderReturn;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(            
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Backend\Helper\Data $backendHelper,        
        array $data = []
    ) {         
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

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
        $collection = $objectManager->create('Ced\Jet\Model\OrderReturn')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */
    public function _prepareColumns()
    {
        $this->addColumn(
            'reference_order_id',
            [
                'header' => __('Reference Order Id'),
                'type' => 'text',
                'index' => 'reference_order_id'
            ]
        );
         $this->addColumn(
            'merchant_order_id',
            [
                'header' => __('Merchant Order Id'),
                'type' => 'text',
                'index' => 'merchant_order_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
               
      $this->addColumn(
                'status',
                [
                        'header' => __('Status'),
                        'index' => 'status',
                        'type' => 'options',
                         'options' => ['created'=>'created', 'inprogress' =>'inprogress','completed'=>'completed']
                ]
                );
        $this->addColumn(
            'returnid',
            [
                'header' => __('Return Id'),
                'index' => 'returnid',
                'type' => 'text'
                ]
        );
       
        
       
        $this->addColumn(
            'Edit',
            [
                'header' => __('View'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View Details'),
                        'url' => [
                            'base' => '*/*/edit'
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
 
        return parent::_prepareColumns();
    }
 
    /**
     * @return $this
     */
    public function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
 
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]   
        );
 
        return $this;
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
            'jet/*/edit',
            ['id' => $row->getId()]
        );
    }
}
