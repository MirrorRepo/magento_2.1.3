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
namespace Ced\Jet\Controller\Adminhtml\Jetrequest;

class Updatestatus extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Update Status constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
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
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @return void
     */

    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Update Product status on jet
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

    public function execute()
    {  
        $result = [];
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection());
        $ids = $collection->getAllIds();
        if (!is_array($ids) && $ids != '') {
           $ids = [$ids];
        }

        if (!empty($ids)) {
            foreach ($ids as  $id) {
                $sku = $this->_objectManager->get('Magento\Catalog\Model\Product')->load(intval($id))->getSku();
                $prodArray["entity_id"] = intval($id);
                $prodArray["sku"] = trim($sku);
                $result[] = $prodArray;
            }
        }

        foreach ($result as $value) {
            $sku = $this->_objectManager->get(
            'Ced\Jet\Helper\Data')->CGetRequest('/merchant-skus/' .rawurlencode($value['sku']));
            $response = json_decode($sku, true);
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($value['entity_id']);
            if (isset($response['merchant_sku'])) {
                $response['status'] = isset($response['status']) ? $response['status'] : "";
                $this->setStatus($response['status'], $product);
            } else if (empty($response)) {
                $product->setJetProductStatus('not_uploaded')->save();
            } else if (isset($response['Message'])) {
                break;
            }
            $this->_objectManager->get('Magento\Framework\Registry')->unregister('prev_sku');
        }
        $this->messageManager->addSuccess("Status sync with jet");
        $this->_redirect('jet/jetrequest/uploadproduct');
    }

    /**
     * @param $status
     * @param $product
     * @return void
     */
    public function setStatus($status, $product)
    {
        switch ($status) {
            case "Under Jet Review":
                $product->setJetProductStatus('under_jet_review')->save();
                break;
            case 'Available for Purchase':
                $product->setJetProductStatus('available_for_purchase')->save();
                break;
            case 'Missing Listing Data':
                $product->setJetProductStatus('missing_listing_data')->save();
                break;
            case 'Archived':
                $product->setJetProductStatus('archived')->save();
                break;
            case 'Excluded':
                $product->setJetProductStatus('excluded')->save();
                break;
            case 'Unauthorized':
                $product->setJetProductStatus('unauthorized')->save();
                break;
            default:
                $product->setJetProductStatus('under_jet_review')->save();
                break;
        }
        return;
    }

    /**
     * Check permissions for this controller
     *
     * @return boolean
     */

    public function _isAllowed()
    {
        return true;
    }

}
