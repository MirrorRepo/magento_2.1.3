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

namespace Ced\Jet\Block\Adminhtml\Vanishedproduct;

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
        $this->setId('gridGrid');
    }

    /**
     * @return $this
     */
    public function _prepareCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $raw_encode = rawurlencode('Under Jet Review');
        $response = $objectManager->create('Ced\Jet\Helper\Data')->CGetRequest('/portal/merchantskus?from=0&size=50000&statuses='.$raw_encode);
        $productObj = $objectManager->get('Magento\Catalog\Model\Product');
        $productCollection = $productObj->getCollection();
        $result = json_decode($response, true);
        $skus = $magentoSkus = [];
        if(isset($result['merchant_skus']) && !empty($result['merchant_skus'])){
            foreach ($result['merchant_skus'] as $sku) {
                 $skus[] = $sku['merchant_sku'];
            }
        }
        $loadData = $productCollection->addAttributeToSelect('sku');
        foreach ($loadData as $product) {
            $magentoSkus[] = $product->getSku();
        }
        $vanishedSkus = !empty($skus) && !empty($magentoSkus) ? array_diff($skus, $magentoSkus) : [];
        $coll = $objectManager->create('\Magento\Framework\Data\Collection');
        foreach ($vanishedSkus as $vSku) {
            $data = $objectManager->create('Ced\Jet\Helper\Data')->CGetRequest('/merchant-skus/'.$vSku);
            $productData = json_decode($data, true);
            $dataObject = $objectManager->create('\Magento\Framework\DataObject');
            $dataObject->setBrand($productData['brand']);
            $dataObject->setMerchantSku($productData['merchant_sku']);
            $dataObject->setPrice($productData['price']);
            $dataObject->setProductTitle($productData['product_title']);
            $coll->addItem($dataObject);
        }

        $this->setCollection($coll);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */
    public function _prepareColumns()
    {
        $this->addColumn('merchant_sku',
            [
                'header'    => __('Merchant Sku'),
                'index'     => 'merchant_sku',
                'filter'    => false,
                'sortable'  => false,
            ]
        );

        $this->addColumn('product_title',
            [
                'header'    => __('Product Title'),
                'index'     => 'product_title',
                'filter'    => false,
                'sortable'  => false,
            ]
        );

        $this->addColumn('brand',
            [
                'header'    => __('Brand'),
                'index'     => 'brand',
                'filter'    => false,
                'sortable'  => false,
            ]
        );

        $this->addColumn('price',
            [
                'header'    => __('Price'),
                'index'     => 'price',
                'filter'    => false,
                'sortable'  => false,
            ]
        );

        $this->addColumn('action',
            [
                'header'    => __('Is Archived'),
                'type'      => 'action',
                'getter'    => 'getMerchantSku',
                'actions'   => [
                    [
                        'caption' => __('Send Archived Request'),
                        'url' => [
                            'base' => 'jet/jetproduct/archiveproduct'
                        ],
                        'field' => 'merchant_sku'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     
    public function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'     => __('Delete'),
                'url'       => $this->getUrl('jet/jetproduct/massDelete'),
                'confirm'   => __('Are you sure?')
            ]
        );
        return $this;
    }*/

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('jet/jetproduct/vanishedproduct', ['_current' => true]);
    }
}
