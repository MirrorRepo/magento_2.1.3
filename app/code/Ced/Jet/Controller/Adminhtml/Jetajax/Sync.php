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

namespace Ced\Jet\Controller\Adminhtml\Jetajax;

class Sync extends \Magento\Backend\App\Action
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
     * @var int
     */
    public $bulk_invprice_batch = 50;

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
    }

    /**
     * Product sync
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $jet_helper =  $this->_objectManager->get('Ced\Jet\Helper\Jet');
        $jet_helper->createuploadDir();
        $coll_data = $jet_helper->getAllJetUploadableProduct();
        if (!empty($coll_data)) {
            $productrows = array_chunk($coll_data, $this->bulk_invprice_batch);
            $this->_objectManager->create('Magento\Backend\Model\Session')->setSyncChunks($productrows);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_Jet::upload_products');
            $resultPage->getConfig()->getTitle()->prepend(__('Sync Products On Jet'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__('No Product available for sync process.'));
            $this->_redirect('jet/jetrequest/uploadproduct');
        }
    }
}
