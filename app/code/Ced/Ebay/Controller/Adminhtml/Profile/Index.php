<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Ebay
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Ebay\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /*if(!$this->_objectManager->create('\Ced\Ebay\Helper\Data')->checkForConfiguration()) {
            $this->messageManager->addNoticeMessage(__('Ebay API not enabled or Invalid. Please check Ebay Configuration.'));
        }*/
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Ebay::ebay_profile');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Profile'));
        return $resultPage;
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Ebay::ebay_profile');
    }
}