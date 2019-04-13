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

namespace Ced\Jet\Controller\Adminhtml\Jetajax;

class Massarchived extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    public $bulk_archive_batch = 500;

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
     * Product Archive
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $jet_helper = $this->_objectManager->get('Ced\Jet\Helper\Jet');
        $coll_data = $jet_helper->getAllJetUploadableProduct(true);

        if (count($coll_data) > 0) {
            $jet_helper->createuploadDir();
            $productids = array_chunk($coll_data, $this->bulk_archive_batch);
            $this->_objectManager->create('Magento\Backend\Model\Session')->setProductArcChunks($productids);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_Jet::upload_products');
            $resultPage->getConfig()->getTitle()->prepend(__('Bulk Product Archive'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__('No Product available for Archive.'));
            $this->_redirect('jet/jetrequest/uploadproduct');
        }
    }
}
