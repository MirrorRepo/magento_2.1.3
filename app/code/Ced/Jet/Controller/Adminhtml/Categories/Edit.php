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
namespace Ced\Jet\Controller\Adminhtml\Categories;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{

    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */

    public $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */

    public $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     *
     */

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @var \Magento\Backend\Model\View\Result\Page $resultPage
     */

    public function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Jet::Jet_categories')
            ->addBreadcrumb(__('Jet Categories'), __('Jet Categories'))
            ->addBreadcrumb(__('Jet Categories'), __('Jet Categories'));
        return $resultPage;
    }

    /**
     * Category Edit page
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function execute()
    {

        $id = $this->getRequest()->getParam('id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Post') : __('New Csv Upload'),
            $id ? __('Edit Post') : __('New Csv Upload'));
        return $resultPage;
    }
}
