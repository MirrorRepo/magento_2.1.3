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

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var
     */
    public $_objectManager;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    public $resultRedirectFactory;
    /**
     * @var
     */
    public $messageManager;
    /**
     * @var
     */
    public $csvProcessor;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @return void
     */

    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager

    )
    {
        $this->messageManager = $messageManager;

    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $mediaDirectory = $this->_objectManager->get(
            '\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $categoryObj = $this->_objectManager->create('Ced\Jet\Model\Categories');
        $collection = $categoryObj->getCollection();
        $csvhandler = $this->_objectManager->create('Ced\Jet\Model\CsvHandler');
        if (isset($data['back'])) {
            $str = file_get_contents("pub/static/restore.json");
            $file = explode(',', $str);
            $index = count($file) - 3;
            $strMapdata = file_get_contents("pub/static/attributemapping.json");
            $fileMapdata = explode(',', $strMapdata);
            $indexMapdata = count($fileMapdata) - 3;
            $strAttribute = file_get_contents("pub/static/attribute.json");
            $fileAttribute = explode(',', $strAttribute);
            $indexAttribute = count($fileAttribute) - 3;
            if ($this->checkIndex($index, $indexMapdata, $indexAttribute)) {
                $files['tmp_name'] = $mediaDirectory->getAbsolutePath('ced/jet/csv/' . $file[$index]);
                $taxonomy = $csvhandler->readFromCsvFile($files);
                $filesMapdata['tmp_name'] = $mediaDirectory->getAbsolutePath('ced/jet/csv/' . $fileMapdata[$index]);
                $taxonomyMapping = $csvhandler->readFromCsvFile($filesMapdata);
                $filesAttrdata['tmp_name'] = $mediaDirectory->getAbsolutePath('ced/jet/csv/' . $fileAttribute[$index]);
                $taxonomyAttribute = $csvhandler->readFromCsvFile($filesAttrdata);
                $this->saveCategoryData($categoryObj, $taxonomy, $taxonomyAttribute, $taxonomyMapping);
                $this->messageManager->addSuccess("Csv has been restored successfully");
            } else {
                $this->messageManager->addSuccess("Updated CSVs already uploaded");
            }
        } else {
            $taxonomy = $taxonomyAttribute = $taxonomyMapping = [];
            $path = $mediaDirectory->getAbsolutePath('ced/jet/csv');
            try {
                $uploader = $this->_objectManager->create('\Magento\MediaStorage\Model\File\Uploader', [
                    'fileId' => 'category_csv']);
                $uploader->setAllowedExtensions(['csv', 'xlsx']);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $files = $uploader->validateFile();
                if (isset($files['name'])) {
                    $extension = pathinfo($files['name'], PATHINFO_EXTENSION);
                    $fileName = $files['name'] . time() . '.' . $extension;
                    $collection->addFieldToFilter('magento_cat_id', ['neq' => '0']);
                    $mapping = [];
                    foreach ($collection as $val) {
                        $mapping[] = [
                            'cat_id' => $val->getCatId(), 'magento_cat_id' => $val->getMagentoCatId()];
                    }
                    $restorefile = fopen("pub/static/restore.json", 'a+');
                    fwrite($restorefile, $fileName . ',');
                    fclose($restorefile);
                    $myfile = fopen("pub/static/catmapping.json", 'w');
                    fwrite($myfile, json_encode($mapping));
                    fclose($myfile);
                    $taxonomy = $csvhandler->readFromCsvFile($files);
                    $uploader->save($path, $fileName);
                }
                $uploader = $this->_objectManager->create('\Magento\MediaStorage\Model\File\Uploader', [
                    'fileId' => 'attribute_csv']);
                $uploader->setAllowedExtensions(['csv', 'xlsx']);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $files = $uploader->validateFile();
                if (isset($files['name'])) {
                    $extension = pathinfo($files['name'], PATHINFO_EXTENSION);
                    $fileName = $files['name'] . time() . '.' . $extension;
                    $restorefile = fopen("pub/static/attribute.json", 'a+');
                    fwrite($restorefile, $fileName . ',');
                    fclose($restorefile);
                    $taxonomyAttribute = $csvhandler->readFromCsvFile($files);
                    $uploader->save($path, $fileName);
                }
                $uploader = $this->_objectManager->create('\Magento\MediaStorage\Model\File\Uploader', [
                    'fileId' => 'categoty_attribute_mapping']);
                $uploader->setAllowedExtensions(['csv', 'xlsx']);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $files = $uploader->validateFile();
                if (isset($files['name'])) {
                    $extension = pathinfo($files['name'], PATHINFO_EXTENSION);
                    $fileName = $files['name'] . time() . '.' . $extension;
                    $restorefile = fopen("pub/static/attributemapping.json", 'a+');
                    fwrite($restorefile, $fileName . ',');
                    fclose($restorefile);
                    $taxonomyMapping = $csvhandler->readFromCsvFile($files);
                    $uploader->save($path, $fileName);
                }
                $this->saveCategoryData($categoryObj, $taxonomy, $taxonomyAttribute, $taxonomyMapping);
                $this->messageManager->addSuccess(__("Csv has been uploaded & updated"));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
        $this->_redirect('jet/categories/index');

    }

    /**
     * @param $categoryObj
     * @param $taxonomy
     * @param $taxonomyAttribute
     * @param $taxonomyMapping
     * @return void
     */
    public function saveCategoryData($categoryObj, $taxonomy, $taxonomyAttribute, $taxonomyMapping)
    {
        if ($this->checkTaxonomy($taxonomy, $taxonomyMapping, $taxonomyAttribute)) {
            $collection = $categoryObj->getCollection();
            unset($taxonomy[0]);
            unset($taxonomyMapping[0]);
            unset($taxonomyAttribute[0]);
            $conn = $collection->getConnection();
            $tableName = $collection->getResource()->getMainTable();
            $conn->truncateTable($tableName);
            foreach ($taxonomy as $txt) {
                if (number_format($txt[3], 0, '', '') != 0) {
                    $catId = number_format($txt[0], 0, '', '');
                    $commaSeperatedId = '';
                    foreach ($taxonomyMapping as $txtMap) {
                        if ($catId == number_format($txtMap[1], 0, '', '')) {
                            $commaSeperatedId = $commaSeperatedId . ',' . number_format($txtMap[0], 0, '', '');
                        }
                    }
                    $commaSeperatedAttIds = ltrim($commaSeperatedId, ',');
                    if ($commaSeperatedAttIds != "") {
                        $attributeIds = explode(',', $commaSeperatedAttIds);
                        $commaSepratedNames = [];
                        foreach ($attributeIds as $val) {
                            foreach ($taxonomyAttribute as $txtAttr) {
                                if ($val == number_format($txtAttr[0], 0, '', '')) {
                                    $commaSepratedNames[] = $txtAttr[2];
                                }
                            }
                        }
                        $attributesNames = implode(',', $commaSepratedNames);
                    } else {
                        $attributesNames = '';
                    }
                    $parentId = number_format($txt[2], 0, '', '');
                    $categoryObj = $this->_objectManager->create('Ced\Jet\Model\Categories');
                    $categoryObj->setData('cat_id', $catId);
                    $categoryObj->setData('name', $txt[1]);
                    $categoryObj->setData('parent_cat_id', $parentId);
                    $categoryObj->setData('level', $txt[4]);
                    $categoryObj->setData('is_taxable_product', $txt[5]);
                    $categoryObj->setData('attribute_ids', $commaSeperatedAttIds);
                    $categoryObj->setData('jetattr_name', $attributesNames);
                    $categoryObj->save();
                    unset($attributeIds);
                }
            }
            $this->insertMapdata();
        }
    }

    /**
     * @param $index
     * @param $indexMapdata
     * @param $indexAttribute
     * @return bool
     */

    public function checkIndex($index, $indexMapdata, $indexAttribute)
    {
        if ($index > 0 && $indexMapdata > 0 && $indexAttribute > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $taxonomy
     * @param $taxonomyMapping
     * @param $taxonomyAttribute
     * @return bool
     */

    public function checkTaxonomy($taxonomy, $taxonomyMapping, $taxonomyAttribute)
    {
        if (isset($taxonomy[0]) && isset($taxonomyMapping[0]) && isset($taxonomyAttribute[0])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return void
     */

    public function insertMapdata()
    {
        $categoryObj = $this->_objectManager->create('Ced\Jet\Model\Categories');
        $strCatMap = file_get_contents('pub/static/catmapping.json');
        $catmap = json_decode($strCatMap, true);
        if (isset($catmap[0])) {
            foreach ($catmap as $value) {
                try{
                    $catLoad = $categoryObj->load($value['cat_id'], 'cat_id');
                    if ($catLoad->getId()) {
                        $catLoad->setMagentoCatId($value['magento_cat_id']);
                        $catLoad->save();
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                }
            }
        }
    }
}
