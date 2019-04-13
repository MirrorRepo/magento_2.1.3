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
namespace Ced\Jet\Block\Adminhtml\Refund;

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
     * @param array $data
     */
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
        $this->setId('refundGrid');
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
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$collection = $objectManager->create('Ced\Jet\Model\Refund')->getCollection();
    	$this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
 
    /**
     * @return $this
     */
    public function _prepareColumns()
    {  
    	$this->addColumn('refund_authorization_id', [
				'header' => __('Refund Authorization Id'),
				'index' => 'refund_id',
				'type' => 'text'
		]);
    	
    	$this->addColumn('refund_status', [
    			'header' => __('Refund Status'),
    			'index' => 'refund_status',
    			'type' => 'text'
    	]);
    	
        $this->addColumn(
        	'magento_order_id',
        	[
        		'header' => __('Magento Order Id'),
        		'index' => 'order_item_id',
        		'type' => 'text',
        		]
        );
   
        $this->addColumn('merchant_order_id', [
				'header' => __('Merchant Order Id/Reference Order Id'),
				'index' => 'refund_orderid',
				'type' => 'text' 
		]);
		
        $this->addColumn(
            'View Details',
            [
                'header' => __('View Details'),
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
        $this->addExportType('*/*/exportRefundCsv',__('CSV'));
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
