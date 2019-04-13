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
 * @package   Ced_Ebay
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Ebay\Controller\Adminhtml\Product;

class Additems extends \Magento\Backend\App\Action
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
    const ADMIN_RESOURCE = 'Ced_Ebay::product';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
    }

    /**
     * Product Mass Upload 
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $helper =  $this->_objectManager->get('Ced\Ebay\Helper\Ebay');
        $result = $this->_objectManager->create('Ced\Ebay\Model\Profileproducts')->getCollection()->getData();
        foreach ($result as $val) {
            $ids[] = $val['product_id'];
        }
        $batchSize = $this->scopeConfigManager->getValue('ebay_config/product_upload/chunk_size');

        if (!empty($ids)) {
            $productids = array_chunk($ids, $batchSize);
            $this->_objectManager->create('Magento\Backend\Model\Session')->setProductChunks($productids);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_Ebay::product');
            $resultPage->getConfig()->getTitle()->prepend(__('Add Products On eBay'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__('No product available for upload.'));
            $this->_redirect('ebay/product/index');
        }
    }
}
