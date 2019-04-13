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
namespace Ced\Jet\Controller\Adminhtml\Jetrequest;

class Updateinventory extends \Magento\Backend\App\Action
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
     * Update Product Inventory constructor.
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
     * Update Product Inventory on jet
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

    public function execute()
    {
        $success = 0;
        $fail = 0;
        $skus = "";
        $failskus = "";
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection());
        $ids = $collection->getAllIds();
        if (!is_array($ids) && $ids != '') {
           $ids = [$ids];
        }
        foreach ($ids as $id) {
            $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load(intval($id));
            if ($product->getTypeId() == 'simple') {
                $response = $this->_objectManager->get('Ced\Jet\Helper\Jet')->updateonjet($product);
                if (!empty($response)) {
                    $skus .= $product->getSku();
                    $skus .= ",";
                    $success++;
                } else {
                    $failskus .= $product->getSku();
                    $failskus .= ",";
                    $fail++;
                }
            }
        }
        if ($success > 0) {
            $this->messageManager->addSuccess($skus." successfully sync with jet");
        }
        if ($fail > 0) {
            $this->messageManager->addError($failskus." not Sync With Jet. Please sync again");
        }
        $this->_redirect('jet/jetrequest/uploadproduct');
    }
}
