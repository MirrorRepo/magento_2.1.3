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
namespace Ced\Jet\Block\Adminhtml\Categories;

class AttributeGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Module Manager
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    /**
     * AttributeGrid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    )
    {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor.
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
    }

    /**
     * @return $this
     */
    public function _prepareCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mediaDirectory = $objectManager->get(
            '\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $path = $mediaDirectory->getAbsolutePath('code/Ced/Jet/Setup/jetcsv/');
        $csvhandler = $objectManager->create('Ced\Jet\Model\CsvHandler');
        $id = $this->getRequest()->getParam('id');
        $attributeObj = $objectManager->get('Ced\Jet\Model\Jetattributes');
        $categoryObj = $objectManager->get('Ced\Jet\Model\Categories');
        $model = $categoryObj->load($id);
        $attributeIds = $model->getAttributeIds();
        $attribute = explode(',', $attributeIds);
        $fileAttrVal = $path . "Jet_Taxonomy_attribute_value.csv";
        $fileAttr = $path . "Jet_Taxonomy_attribute.csv";
        if (!file_exists($fileAttrVal) || !file_exists($fileAttr)) {
            $this->messageManager->addError('Jet Extension Csv missing please check "Jet_Taxonomy_attribute.csv" ');
            return;
        }
        $filesAttrVal['tmp_name'] = $fileAttrVal;
        $taxonomyAttrVal = $csvhandler->readFromCsvFile($filesAttrVal);
        unset($taxonomyAttrVal[0]);
        $filesAttr['tmp_name'] = $fileAttr;
        $taxonomyAttr = $csvhandler->readFromCsvFile($filesAttr);
        unset($taxonomyAttr[0]);
        $details = [];
        foreach ($taxonomyAttr as $txtAttr) {
            $field = trim($txtAttr[0]);
            if (in_array($field, $attribute)) {
                $details[$field]['name'] = trim($txtAttr[2]);
                $details[$field]['description'] = trim($txtAttr[1]);
                $details[$field]['free_text'] = trim($txtAttr[3]);
                $details[$field]['attr_value'] = '';
                $details[$field]['units'] = '';
                foreach ($taxonomyAttrVal as $txt) {
                    $attrField = trim($txt[0]);
                    if ($field == $attrField) {
                        if ($details[$field]['attr_value'] == "") {
                            if (trim($txt[1]) != "") {
                                $details[$field]['attr_value'] = trim($txt[1]);
                            }
                        } else {
                            if (trim($txt[1]) != "") {
                                $details[$field]['attr_value'] = $details[$field]['attr_value'] . ',' . trim($txt[1]);
                            }
                        }
                        if ($details[$field]['units'] == "") {
                            if (trim($txt[2]) != "") {
                                $details[$field]['units'] = trim($txt[2]);
                            }
                        } else {
                            if (trim($txt[2]) != "") {
                                $details[$field]['units'] = $details[$field]['units'] . ',' . trim($txt[2]);
                            }
                        }
                    }
                }
            }
        }
        $collection = $objectManager->create('\Magento\Framework\Data\Collection');
        foreach ($details as $key => $value) {
            $magentoAttrId = '';
            $status = "Not Created";
            $attributeCollection = $attributeObj->getCollection()->addFieldToFilter('jet_attribute_id', $key);
            if (!empty($attributeCollection)) {
                foreach ($attributeCollection as $at) {
                    $magentoAttrId = $at->getData('magento_attribute_id');
                    break;
                }
            }
            if ($magentoAttrId != "") {
                $status = "Created";
            }
            $dataObj = $objectManager->create('\Magento\Framework\DataObject');
            $dataObj->setJetAttributeId($key);
            $dataObj->setMagentoAttributeId($magentoAttrId);
            $dataObj->setStatus($status);
            $dataObj->setCategory($id);
            $dataObj->setName($value['name']);
            $dataObj->setDescription($value['description']);
            $dataObj->setFreetext($value['free_text']);
            $dataObj->setAttributeValue($value['attr_value']);
            $dataObj->setUnits($value['units']);
            $collection->addItem($dataObj);
        }
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare columns
     * @return $this
     */
    public function _prepareColumns()
    {
        $this->addColumn('jet_attribute_id', [
            'header' => __('Jet Attribute Id'),
            'index' => 'jet_attribute_id',
            'type' => 'int',
            'filter' => false,
            'sortable' => false
        ]);
        $this->addColumn('name', [
            'header' => __('Attribute Name'),
            'index' => 'name',
            'type' => 'text',
            'filter' => false,
            'sortable' => false
        ]);
        $this->addColumn('attribute_value', [
            'header' => __('Attribute Value'),
            'index' => 'attribute_value',
            'type' => 'int',
            'filter' => false,
            'sortable' => false
        ]);
        $this->addColumn('description', [
            'header' => __('Description'),
            'index' => 'description',
            'type' => 'text',
            'filter' => false,
            'sortable' => false
        ]);
        $this->addColumn('units', [
            'header' => __('Attribute Units'),
            'index' => 'units',
            'type' => 'text',
            'filter' => false,
            'sortable' => false
        ]);
        $this->addColumn('freetext', [
            'header' => __('level'),
            'index' => 'freetext',
            'type' => 'text',
            'filter' => false,
            'sortable' => false
        ]);
        $this->addColumn('magento_attribute_id', [
            'header' => __('Magento Attribute Id'),
            'index' => 'magento_attribute_id',
            'type' => 'int',
            'filter' => false,
            'sortable' => false
        ]);
        $this->addColumn('status', [
            'header' => __('Status'),
            'index' => 'status',
            'type' => 'text',
            'filter' => false,
            'sortable' => false
        ]);
        $this->setPagerVisibility(false);
        return parent::_prepareColumns();
    }

    /**
     * Grid Url
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('jet/*/grid', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getMainButtonsHtml()
    {
        return '';
    }
}
