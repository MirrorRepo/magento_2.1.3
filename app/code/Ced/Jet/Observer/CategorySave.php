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

class CategorySave extends \Magento\Payment\Model\Method\AbstractMethod implements ObserverInterface
{
    /**
     *
     * @var ObjectManagerInterface
     */
    public $_objectManager;
    public $model;
    public $payment_data;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * CategorySave constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param RequestInterface $request
     * @param ScopeConfig $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        RequestInterface $request,
        ScopeConfig $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager)
    {
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
        $data = $this->request->getPost();
        $currentCatId = $data['entity_id'];
        $level1cat = (isset($data ['jet_cat_level_1']) || empty($data['jet_cat_level_1'])) ? $data ['jet_cat_level_1'] : 0;
        $level2cat = (isset($data ['jet_cat_level_2']) || empty($data['jet_cat_level_2'])) ? $data ['jet_cat_level_2'] : 0;
        $model = $this->_objectManager->create('Ced\Jet\Model\Categories');
        if (isset($data['jet_cat_level_0']) && $level1cat == 0) {
            $this->checkMagentoCatIdExist($currentCatId);
            return $this->messageManager->addError(__('Select Magento Sub-Category'));
        } else if ($level2cat != 0 || $level1cat != 0) {
            $this->checkMagentoCatIdExist($currentCatId);
            $catId  = $level2cat !=0 ? $level2cat : $level1cat;
            $catInfo = $model->getCollection()->addFieldToFilter('cat_id', $catId)->getFirstItem()->getData();
            $mageCatId = empty($catInfo["magento_cat_id"]) ? $data['entity_id'].',' : $catInfo["magento_cat_id"].$data['entity_id'].',';
            $model = $this->_objectManager->create('Ced\Jet\Model\Categories');
            $model->load($catId, 'cat_id');
            $model->setdata('magento_cat_id', $mageCatId)->save();
            return $this->messageManager->addSuccess(__('Jet Mapping details has been saved'));
        }
    }

    /**
     * @param $currentCatId
     * @return bool
     */
    public function checkMagentoCatIdExist($currentCatId)
    {
        $catCollection = $this->_objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter(['magento_cat_id'], [[ 'like' => "%".$currentCatId.",%" ]])->getData();
        foreach ($catCollection as $mageCatExist) {
            if (isset($mageCatExist['magento_cat_id'])) {
                $catExplode = explode(',', $mageCatExist['magento_cat_id']);
                foreach ($catExplode as $categoryId) {
                    if ($categoryId == $currentCatId) {
                        $temp = array_flip($catExplode);
                        unset($temp[$currentCatId]);
                        $mageCatId = "";
                        if (count($temp) > 1) {
                            $mageCatId = implode(',',array_flip($temp));
                        }
                        $id = $mageCatExist["id"];
                        $model = $this->_objectManager->create('Ced\Jet\Model\Categories');
                        $model->load($id);
                        $model->setdata('magento_cat_id', $mageCatId)->save();
                        return true;
                    }
                }
            }
        }
        return true;
    }
}
