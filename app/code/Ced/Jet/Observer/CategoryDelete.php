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
namespace Ced\Jet\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Magento\Framework;
use \Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use \Magento\Payment\Model\InfoInterface;
use Magento\Framework\App\RequestInterface;

class CategoryDelete extends \Magento\Payment\Model\Method\AbstractMethod implements ObserverInterface {
    /**
     *
     * @var ObjectManagerInterface
     */
    public $_objectManager;
    /**
     * @var
     */
    public $model;
    /**
     * @var
     */
    public $payment_data;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * CategoryDelete constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param RequestInterface $request
     * @param ScopeConfig $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, RequestInterface $request, ScopeConfig $scopeConfig,\Magento\Framework\Message\ManagerInterface $messageManager) {
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $magentoCategory = $this->request->getParam('id');
        $catCollection = $this->_objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter(['magento_cat_id'], [[ 'like' => "%".$magentoCategory.",%" ]])->getData();
        foreach ($catCollection as $mageCatExist) {
            if (isset($mageCatExist['magento_cat_id'])) {
                $catExplode = explode(',', $mageCatExist['magento_cat_id']);
                foreach ($catExplode as $categoryId) {
                    if ($categoryId == $magentoCategory) {
                        $temp = array_flip($catExplode);
                        unset($temp[$magentoCategory]);
                        $mageCatId = "";
                        if (count($temp) > 1) {
                            $mageCatId = implode(',',array_flip($temp));
                        }
                        $id = $mageCatExist["id"];
                        $model = $this->_objectManager->create('Ced\Jet\Model\Categories');
                        $model->load($id);
                        $model->setdata('magento_cat_id', $mageCatId)->save();
                        break;
                    }
                }
            }
        }
    }
}
