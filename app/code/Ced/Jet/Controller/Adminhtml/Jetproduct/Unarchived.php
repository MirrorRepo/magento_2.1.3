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

namespace Ced\Jet\Controller\Adminhtml\Jetproduct;

class Unarchived extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';

    public $filter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
    }

    /**
     * Product uNARCHIVED 
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection());
        $ids = $collection->getAllIds();
        if (!is_array($ids) && $ids != '') {
           $ids = [$ids];
        }
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select product id(es).'));
        } else {
            try {
                $productdata = $this->_objectManager->create('Magento\Catalog\Model\Product');
                $cunArchived = 0;
                foreach ($ids as $id) {
                    $productLoad = $productdata->load($id);
                    if ($productLoad->getTypeId() == 'configurable') {
                        $type = 'configurable';
                    } else {
                        $code = $this->Unarchprocess($productLoad);
                        if ($code != false) {
                            $cunArchived++;
                            $productLoad->setJetProductStatus($code)->save();
                        }
                    }
                }
                if ($cunArchived > 0) {
                    $this->messageManager->addSuccess(__($cunArchived.' product(s) unarchived successfully'));
                } else {
                    $this->messageManager->addError(__('Product(s) Unarchive Request Failed.'));
                }
            } catch(\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('jet/jetrequest/uploadproduct');
    }

    /**
     * Product Unarchive Process 
     *
     * @return String
     */
    public function Unarchprocess($productLoad) {

        $data_helper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $fullfillmentnodeid = $data_helper->getFulfillmentNode();
        if (empty($fullfillmentnodeid) || $fullfillmentnodeid == '' || $fullfillmentnodeid == null) {
            $this->messageManager->addError('Please Enter fullfillmentnode id in Jet Configuration.');
            return false;
        }
        $sku = $productLoad->getSku();

        $inventory = [];
        $qty = $productLoad->getQuantityAndStockStatus()['qty'];
        $inventory['fulfillment_nodes'][] = ['fulfillment_node_id'=>$fullfillmentnodeid, 'quantity'=>$qty];
        $data_helper->CPutRequest('/merchant-skus/'.$sku.'/status/archive', json_encode(['is_archived'=>false]));
        $data_helper->CPutRequest('/merchant-skus/'.$sku.'/inventory', json_encode($inventory));
        $result = $data_helper->CGetRequest('/merchant-skus/'.rawurlencode($sku).'');
        $response = json_decode($result);

        if (isset($response->status)) {
            switch ($response->status) {
                case 'Available for Purchase':
                    $code = 'available_for_purchase';
                    break;
                case 'Archived':
                    $code = 'archived';
                    break;
                case 'Missing Listing Data':
                    $code = 'missing_listing_data';
                    break;
                case 'Under Jet Review':
                    $code = 'under_jet_review';
                    break;
                case 'Excluded':
                    $code = 'excluded';
                    break;
                case 'Unauthorized':
                    $code = 'unauthorized';
                    break;
                default:
                    $code = '';
                    break;
            }
        }
        return $code;
    }
}
