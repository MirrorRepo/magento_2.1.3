require([
            "jquery",
            
        ], function($) {
	
	var val=$('#agreeto_return').val();
		//alert(val);
		var skucount = $('#sendid').val();
		if (val == 1)
			{
				$('#return_refundfeedback0').parent().hide();
			}else
			{
				$('#return_refundfeedback0').parent().show();
				
			}
		
		$('#agreeto_return').change(function() {
			val = $('#agreeto_return').val();
			for(var i=0 ; i<skucount ;i++) {
				if (val == 1)
				{
					$('#return_refundfeedback'+i).parent().hide();
					$('#return_refundfeedback'+i).parent().parent().hide();
			
				}else
				{
					$('#return_refundfeedback'+i).parent().parent().show();
					$('#return_refundfeedback'+i).parent().show();
					//alert('else');
				}
			}
		});
	
		
});
	        	