<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/app/bootstrap.php';


class Cronerror extends Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError
{
  function __construct()
  {
    //  $this->errorMessage = $errorMessage;
  }
  public function getMessgae()
  {
    return $this->getErrorMessage();
  }
}




$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
//$objectManager->get('Magento\Framework\App\Filesystem\DirectoryList')->getPath('media');

//  $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
     function execute($objectManager,$storeManager)
      {
         $objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');
        $import = $objectManager->get('Magento\ImportExport\Model\ImportFactory')->create();
        //  $import_agg = $objectManager->get('\Magento\ImportExport\Model\Import')->create();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron_products.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $import_path = "/home/dulcefin/public_html/demo/var/import/cron_products.csv";
        if(file_exists($import_path))
        {
        $import_file = pathinfo($import_path);
        $import->setData(
            array(
                'entity' => 'catalog_product',
                'behavior' => 'append',
                'validation_strategy' => 'validation-skip-errors',
                'allowed_error_count'=>10000
                 )
               );


        $read_file = $objectManager->get('Magento\Framework\Filesystem\Directory\ReadFactory')->create($import_file['dirname']);

        $csvSource = $objectManager->get('Magento\ImportExport\Model\Import\Source\CsvFactory')->create(
            array(
                'file' => $import_file['basename'],
                'directory' => $read_file,
            )
        );
          //echo "<pre>";
          $res=$objectManager->get('Magento\Framework\File\Csv')->getData($import_path);


        $validate = $import->validateSource($csvSource);
        if (!$validate) {
          $logger->info('Unable to validate the CSV');
          $msg="Unable to validate the CSV";
        mail_send($msg,$objectManager,$storeManager);
             //die("unvalid");
        }

        $errorData=array();
        $messageErrorData =array();
        $remove_product=array();
        $mailmsg="";
          try {
            $import->importSource();
              $import->invalidateIndex();
            //  die("test");
              $logger->info('Finished importing products from '.$import_path);
              $skipped =$import->getErrorAggregator()->getSkiprows();
              $invalid_rows =$import->getErrorAggregator()->get_invalid_rows();
              $result =$import->getErrorAggregator()->getAllErrors();
              $get_codes =$import->getErrorAggregator()->get_codes();
              //echo "<pre>";
            //  print_r($import->getErrorAggregator());
              // print_r($invalid_rows);
              // print_r($result);
              // print_r($get_codes);
              if(!empty($result))
              {
                  $errorData = (array)$result[0];
              }


          } catch (\Exception $e) {
            $logger->info($e->getMessage());
            $msg=(string)$e->getMessage();
          }
                  //print_r($errorData);
                    if(!empty($result)){
                      foreach($result as $key1=>$err_arr)
                      {

                        //print_r((array)$err_arr);
                        foreach ((array)$err_arr as $key => $value) {
                              $messageErrorData[trim(str_replace("*","",$key))] =$value;
                        }
                        array_push($remove_product, $messageErrorData);
                      //  print_r($messageErrorData);
                      }


                      $sku=" Skipped rows ";


                      $error="";
                      // foreach ($invalid_rows as $key1 => $value1) {
                        foreach ($remove_product as $key => $value) {
                              // if($value1==$value['rowNumber']){
                            //  echo $import->addErrorMessageTemplate($value,$value);
                              $row_no=(int)$value['rowNumber']+2;
                                $error.=$value['errorMessage']." ROW(".$row_no.")";
                                if($res[$value['rowNumber']+1][0])
                                {
                                $error.=" SKU(".$res[$value['rowNumber']+1][0].")<br/>\r\n";
                                }else{
                                  $error.="<br/>\r\n";
                                }
                              }

                              //  echo $error;
                        $logger->info($error);
                //  die();
                }else {
                  echo $error ="Update Succesfully";

                }
              //  unlink("/home/dulcefin/public_html/demo/var/import/cron_products.csv");
                mail_send($error,$objectManager,$storeManager);
              }
      //  }

  //die("qq3");


    }

    function mail_send($message,$objectManager,$storeManager)
    {
      //info@dulcefina.com
      $to = "info@demo.dulcefina.com";
      $date=date('d-M-Y');
      $subject = "Upload Products (Cron Sheduled".$date.")";

      // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

  // More headers
  $headers .= 'From: info@demo.dulcefina.com' . "\r\n";
//  $headers .= 'Cc: myboss@example.com' . "\r\n";

   mail($to,$subject,$message,$headers);

    }

  execute($objectManager,$storeManager);
  //mail_send("hello",$objectManager,$storeManager)

?>
