jQuery(document).ready(function($) {
	$('#pow-choose-region').change(function(){
			$('#pow-region').html( $('#pow-choose-region option:selected').text() );
			var region_id = $(this).val();
			var nonce = $(this).attr('data-nonce');
	        $.ajax({
	            type: 'POST',
	            dataType: 'json',
	            url: ajaxurl,
	            data: { 'action' : 'getpow', 'nonce' : nonce, 'region_id' : region_id },
	            success:function( resp ){
	            	var content = '';
	            	$('#pow-content').html('<p class="text-center" style="margin-top:50px">Please wait...</p>');
	            	if( resp.length > 0 )
	            	{
	            		for( var i=0; i<resp.length; i++)
	            		{
	            			content += '<div class="row"><div class="col-xs-4 text-center"><a href="'+resp[i].url+'" target="_blank"><img src="'+resp[i].picture+'" alt="'+resp[i].title+'" class="pow-thumb"></a></div><div class="col-xs-8"><a href="'+resp[i].url+'" target="_blank">'+resp[i].title+'</a><div class="snippet">'+resp[i].snippet+'</div></div></div>';
	            		}
	            	}
	            	$('#pow-content').html( content );
	            }
	        });		
	});
});