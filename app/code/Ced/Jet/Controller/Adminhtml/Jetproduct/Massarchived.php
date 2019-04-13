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

class Massarchived extends \Magento\Backend\App\Action
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
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Massarchived constructor.
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\App\Action\Context $context,        
        \Magento\Ui\Component\MassAction\Filter $filter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
    }

    /**
     * Product MassArchived 
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
                $cArchived = 0;
                $cClosed = 0;

                foreach ($ids as $id) {
                    $productLoad = $productdata->load($id);
                    if ($productLoad->getTypeId() == 'simple') {
                        $sku = $productLoad->getSku();
                        $result = $this->_objectManager->get('Ced\Jet\Helper\Data')->CGetRequest('/merchant-skus/'.rawurlencode($sku));
                        $response = json_decode($result, true);
                        if (isset($response['is_archived']) && !empty($response['is_archived'])) {
                            $cArchived++;
                        } else {
                            $cClosed++;
                            $data2 = $this->_objectManager->get('Ced\Jet\Helper\Data')
                                    ->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode(['is_archived'=>true]));
                            $productLoad->setJetProductStatus('archived')->save();
                        }
                    } else {
                        // @todo for configurable product
                    }
                }
                if ($cClosed > 0 || $cArchived > 0) {
                    if ($cClosed > 0) {
                        $this->messageManager->addSuccess(__($cClosed.' product(s) is archived successfully'));
                    }
                    if ($cArchived > 0) {
                        $this->messageManager->addError(__($cArchived.' product(s) is already archived'));
                    }
                } else {
                    $this->messageManager->addError(__('Product(s) Archived Request Failed.'));
                }
            }catch(\Exception $e)
            {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('jet/jetrequest/uploadproduct');
    }
}
