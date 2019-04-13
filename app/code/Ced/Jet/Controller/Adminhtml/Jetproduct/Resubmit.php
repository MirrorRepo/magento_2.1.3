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

class Resubmit extends \Magento\Backend\App\Action
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
    const ADMIN_RESOURCE = 'Ced_Jet::rejected_files';

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
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $jfile_id = $this->getRequest()->getPost('jetinfofile_id');
        $error_fileid = $this->getRequest()->getPost('id');
        $loadfile = $this->_objectManager->create('Ced\Jet\Model\Fileinfo')->load($jfile_id);
        $products = $loadfile->getMagentoBatchInfo();

        $model_error = $this->_objectManager->create('Ced\Jet\Model\Errorfile')->load($error_fileid);
        if ($model_error->getId()) {
            $model_error->setStatus('Resubmit Requested');
            $model_error->save();
            $this->messageManager->addSuccess('Inventory File submission successfully done.');
        }
        $this->_redirect("jet/jetproduct/massimport", ["resubmited_product"=>$products]);
    }
}
