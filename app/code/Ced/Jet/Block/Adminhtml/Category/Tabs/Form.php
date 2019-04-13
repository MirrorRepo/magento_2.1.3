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
namespace Ced\Jet\Block\Adminhtml\Category\Tabs;

/**
 * Blog post edit form main tab
 */
class Form extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $_systemStore;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    public $_wysiwygConfig;

    /**
     * @var string
     */
    public $_template = 'categories/form.phtml';

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Catalog $helperCatalog,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        $this->_helperCatalog = $helperCatalog;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return mixed
     */

    public function getCurrentCategory()
    {
        return $this->_coreRegistry->registry('current_category')->getEntityId();
    }

    /**
     * @return mixed
     */
    public function getRootCategory()
    {
        return $this->_coreRegistry->registry('current_category')->getParentId();
    }

    /**
     * @param $level
     * @return mixed
     */
    public function getLevel($level)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection=$objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter('level',$level);
        return $collection->getData();
    }

    /**
     * @param $level
     * @return string
     */
    public function getSavedCategoryData($level)
    {
        $magento_cat_id=$this->getCurrentCategory();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection=$objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter(['magento_cat_id'], [[ 'like' => "%".$magento_cat_id.",%" ]])
            ->addFieldToFilter('level',$level);
        $cat_ids=[];
        $cat_id = "";
        foreach ($collection as $val) {
            $cat_ids = explode(',', $val->getMagentoCatId());
            foreach ($cat_ids as $value) {
                if ($value == $magento_cat_id) {
                    $cat_id = $val->getCatId();
                }
            }
        }
        return $cat_id;
    }

    /**
     * @param $level
     * @return bool
     */
    public function getParentCategoryData($level)
    {
        $magento_cat_id=$this->getCurrentCategory();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection=$objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter(['magento_cat_id'], [[ 'like' => "%".$magento_cat_id.",%" ]])
            ->addFieldToFilter('level',$level);
        $cat_ids=[];
        foreach ($collection as $val) {
            $cat_ids = explode(',', $val->getMagentoCatId());
            foreach ($cat_ids as $value) {
                if ($value == $magento_cat_id) {
                    $parent_cat_id = $val->getParentCatId();
                }
            }
        }
        return isset($parent_cat_id) ? $parent_cat_id : false;
    }

    /**
     * @param $cat_id_level1
     * @return bool
     */
    public function getRootCategoryData($cat_id_level1)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection=$objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter('cat_id',$cat_id_level1)
            ->addFieldToFilter('level',1);

        foreach ($collection as $val)
        {
            $root_cat_id  = $val->getParentCatId();
        }

        return isset($root_cat_id)?$root_cat_id:false;
    }

    /**
     * @return bool
     */
    public function getsavedMagentoIds()
    {
        $current_cat = $this->getCurrentCategory();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection=$objectManager->create('Ced\Jet\Model\Categories')->getCollection()
            ->addFieldToFilter('level',2);

        foreach ($collection as $val)
        {
            if ($val->getMagentoCatId() && $val->getMagentoCatId()!=$current_cat)
            {
                $savedMagentoIds[$val->getMagentoCatId()]  = $val->getCatId();
            }
        }
        return isset($savedMagentoIds)?$savedMagentoIds:false;
    }
}
