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

class JerrorDetails extends \Magento\Backend\App\Action
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
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * JerrorDetails constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Jet Error Info
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('id', null);
        $model = $this->_objectManager->create('Ced\Jet\Model\Errorfile');
        if ($id) {
            $model->load($id);
            if (isset($model) && $model->getId()) {
                $data = $this->_objectManager->create('Magento\Backend\Model\Session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addError(__('Choosen Record is Not Found!'));
                $this->_redirect('jet/jetproduct/rejected');
            }
        }
        $this->registry->register('errorfile_collection', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Jet::rejected_files');
        $resultPage->getConfig()->getTitle()->prepend(__('Error File Information'));
        return $resultPage;
    }
}
