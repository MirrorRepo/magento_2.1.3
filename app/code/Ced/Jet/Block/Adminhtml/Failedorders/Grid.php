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
namespace Ced\Jet\Block\Adminhtml\Failedorders;

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
        $this->setDefaultDir('DESC');
        $this->setDefaultSort('id');       
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('grid_record');
    }

    /**
     * @return $this
     */
    public function _prepareCollection()
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $id=$this->getRequest()->getParam('id'); 
        if ($id)
        {
            $collection = $objectManager->create('Ced\Jet\Model\Failedorders')->getCollection()->addFieldToFilter('id',$id);
            
        } else {
        $collection = $objectManager->create('Ced\Jet\Model\Failedorders')->getCollection();
        }
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
    			'reference_number',
    			[
    					'header' => __('Jet Reference Order Id'),
    					'type' => 'text',
    					'index' => 'reference_number',
    					'header_css_class' => 'col-id',
    					'column_css_class' => 'col-id'
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
            'reason',
            [
                'header' => __('Reason to Failed'),
                'index' => 'reason'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'text'
                ]
        );        
        $this->addColumn(
            'order_time',
            [
                'header' => __('Order Place Time'),
                'index' => 'order_time',
                'type' => 'datetime'
                ]
        );
        $this->addColumn(
            'cancel_order',
            [
                'header' => __('Cancel Order'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                        [
                            'caption' => __('Cancel Order'),
                            'url' => [
                                    'base' => '*/*/cancel'
                            ],
                            'field' => 'id'
                        ]
                ],
                'filter' => false,
                'sortable' => false,
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
                'url' => $this->getUrl('jet/*/massDelete'),
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
}
