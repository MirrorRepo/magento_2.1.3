<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 *
 */
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
class Cronerror
{
protected $errorMessage;
  function __construct()
  {
    //  $this->errorMessage = $errorMessage;
  }

  public function getErrorMessage()
  {
      return $this->errorMessage;
  }
}

$obj_error= new Cronerror();
echo $obj_error->getErrorMessage();
