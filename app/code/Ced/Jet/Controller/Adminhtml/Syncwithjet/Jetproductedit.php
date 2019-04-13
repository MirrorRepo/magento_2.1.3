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
namespace Ced\Jet\Controller\Adminhtml\Syncwithjet;

use \Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;

class Jetproductedit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */

    public $resultPageFactory;

    /**
     * @var
     */
    public $messageManager;

    /**
     * Jetproductedit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfig $scopeConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ScopeConfig $scopeConfig

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $postId = $this->getRequest()->getParam('id');
        $response = [];
        $product = $this->_objectManager->create(
            'Magento\Catalog\Model\Product')->load($postId);
        if ($product->getTypeId() == 'simple') {
            $response = $this->_objectManager->get('Ced\Jet\Helper\Jet')->updateonjet($product);
            if (!empty($response)) {
                $response = str_replace(" ", ",", $response);
                $msg = $response." Successfully Sync With Jet";
                $this->messageManager->addSuccess($msg);
            }
        }
        return  $this->_redirect('catalog/product/edit', ['id' => $postId, '_current' => true]);
    }
}
