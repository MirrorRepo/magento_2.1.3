<!doctype html>
<html lang="en-US">
<head>
	<script src="http://112.196.17.55/commands/css-js/jquery.min.js"></script>
	<link rel="stylesheet" href="http://112.196.17.55/commands/css-js/bootstrap.min.css">
	<script src="http://112.196.17.55/commands/css-js/bootstrap.min.js"></script>
</head>
 <body>
<div class="container-fluid">
<div class="row">

<div class="jumbotron text-center">
  <h1> List of commands</h1>
  

<div class="row ">
<form action="" method="post" class="form-inline">
<div class="form-group">
  <label for="sel1">Select Command: </label>
  <select class="form-control" id="command-list" name="commad_id" onchange="onChangeCommand(this.value)">
    <option selected="selected">Please Select</option>
    <option value="1">Refresh Cache</option>
    <option value="2">Re-index</option>
    <option value="3">Remove Var Folder</option>
    <option value="4">Set 777 Permission Var Folder</option>
    <option value="5">Enable Extension</option>
    <option value="6">List of Enable/Disable Extension</option>
   
  </select>
  <input type="text" name="extension_name" class="hidden form-control" placeholder="Enter Extension name"  >
</div>
<input type="submit" name="show_command" class="btn btn-primary" value="Show Command">
</form>
</div>
</div>
</div>
<?php 
if($_POST)
{
	    $shelldir  = "/var/www/html/commands/dulcefina";
	    $projectDirectory = "/var/www/html/dulcefina";
		$fileName = "/commands.sh";
	    $fileLocation = $shelldir.$fileName;
		if(!file_exists($fileLocation)) {
		die("<div class='alert alert-danger'>File not found ".$fileLocation."</div>");
		}
	if(isset($_POST['commad_id']) && is_numeric($_POST['commad_id'])) //check show command is click
	{
		$commandId = $_POST['commad_id'];
		$myfile = fopen($fileLocation, "w") or die("<div class='alert alert-danger'>Unable to open file ".$fileLocation."</div>");
		$shellCommand = "#!/bin/sh \n"  ;
		$extensionName  = $_POST['extension_name'];
		$magentoCommandDir = 'php '.$projectDirectory.'/bin/magento';
		$magentoCommandsRemoveVar = "rm -r ".$projectDirectory.'/var/*; '."\n".'ls -all '.$projectDirectory.'/var/';
		$magentoCommandsSetPermissionVar = "chmod 777 -R ".$projectDirectory.'/var/;'."\n".'ls -all '.$projectDirectory.'/var/';
		
		$commandList = array(
		                   1=>$shellCommand.$magentoCommandDir.' cache:clean',
		                   2=>$shellCommand.$magentoCommandDir.' index:reindex',
		                   3=>$shellCommand.$magentoCommandsRemoveVar,
		                   4=>$shellCommand.$magentoCommandsSetPermissionVar,
		                   5=>$shellCommand.$magentoCommandDir.' module:enable '.$extensionName.';'."\n".$magentoCommandDir.' setup:upgrade',
		                   6=>$shellCommand.$magentoCommandDir.' module:status'
		                   
		                   
		);
		$commandWrite  = $commandList[$commandId];
		fwrite($myfile, $commandWrite);
		fclose($myfile);
		echo '<div class="alert alert-info"><pre>';
		readfile($fileLocation);
		echo "</div>";
		?>
	<div class="row jumbotron text-center " id="rum-command">
	<form action="" method="post" class="form-inline">
	<input type="submit" name="run_command" class="btn btn-primary" value="Run Commands">
	</form>
	</div>
	<script>
	/* selectedLastOption function start */
	function selectedLastOption(selectedId,optionValue){
		jQuery("#"+selectedId).val(optionValue).prop('selected',true);
	}
	selectedLastOption("command-list",<?=$commandId; ?>);
	/* selectedLastOption function end */
	/* enable extesnion function start */
	
	/* enable extesnion function end */
	
	</script>
<?php 	
	}else {
		if(isset($_POST['run_command']))//check run command is click
		{
			
			$output = shell_exec($fileLocation);  // run shell command
		    die("<div class='alert alert-success'><pre>$output</div>");	
		}
		die("<div class='alert alert-danger'>Please Select Command</div>");
	}
}
?>
<script>
	function onChangeCommand(val)
	{
		jQuery('#rum-command').empty();
		if(val == 5)
		{
			jQuery('input[name=extension_name]').attr('required' , 'required').removeClass('hidden');
		}else{
			jQuery('input[name=extension_name]').removeAttr('required').addClass('hidden');
		}
	}

</script>
</div>
</body>
</html>
