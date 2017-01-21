jQuery(document).ready(function($){
	$.noConflict();
	
	  $(function() {
    $( "#sortable" ).sortable();
    //$( "#sortable" ).disableSelection();
  });
  
/*************** Color Picker ******************/
$('#menu_item_color').wpColorPicker();
$('#menu_item_hover_color').wpColorPicker();
$('#logout_color').wpColorPicker();
$('#logout_hover_color').wpColorPicker();
$('#logout_bg_color').wpColorPicker();
$('#logout_hover_bgcolor').wpColorPicker();

	
	
	var i=0;
	$('.add_endpoint').on('click',function(){
		
		$('.add_endpoint').before('<li class="adding_endpoint" style="border: 1px solid #dfdfdf;"><div class="new-endpoint-form"><table class="form-table"><tbody><tr><th><label for="phoen-new-endpoint">Menu Name</label></th><td class=""><input type="text" value="" class="endpoint_input" name="phoen-new-endpoint"  id="phoen-new-endpoint-'+i+'" required><span class="checking"></span><p class="error-msg"></p></td></tr></tbody></table><input type="submit" class="button add" name="add"  value="Add Menu" disabled="disabled"></div></li>');
		i++;
	});
	
	$('li .header').click(function(){
		
		//$(this).find('div').show();
		
		if($(this).next('div').css('display')!='none'){
			$(this).next('div').hide();
		}else{
			$(this).next('div').show();
		}
	
		
	});

	/******* Sort Endpoint ********/
	
	update_list_endpoint = function(){
		var fields = new Array();
			
			$('li .phoen-endpoint-order').each(function(i){

				fields[i] = $(this).val();
		});
			$( 'input.endpoints-order' ).val( fields.join(',') );
		
	};
	
	$('.endpoints li').mouseleave(function(){
		//alert();
		update_list_endpoint();	
	});
	
	$('#submit').click(function(){
		
		update_list_endpoint();	
	});
	

$('.pho-user-image').click(function(){
	$('.pho-popup-body').show();
	
});
$('.pho-close_btn').click(function(){
	$('.pho-popup-body').hide();
	
});
	
});

var xhr = null;
jQuery(document).on('keyup','.endpoint_input', function(){
	var input = jQuery(this),
	li_adding       = input.closest( '.adding_endpoint' );
 //var id = jQuery(this).attr('id');
// alert(id);
if(jQuery(this).val()=='')
{
li_adding.find('.checking img').remove();
li_adding.find('.add').attr("disabled","disabled");	
}
	if(jQuery(this).val().length >2){
		var title = jQuery(this).val();
		var length = jQuery(this).val().length;
		
		if( xhr != null ) {
                xhr.abort();
                xhr = null;
        }
		
		
		//alert(length);
		li_adding.find('.checking').html('<img src="'+plugin_url+'images/loading.gif">');
		
	xhr = jQuery.ajax({
			type:'POST',
			url:phoen_myaccount_Ajax.ajax_url,
			data:{
				action:'phoen_myaccount_check',
				title: title
			},
			beforeSend:function(){
				
			},
			success: function(data){
				//return data;
				//alert(data);
		
				if(data==1)
				{
					
					
					li_adding.find(".checking").html('<img src="'+plugin_url+'images/false.png">');
				}
				else 
				{
					
					li_adding.find('.checking').html('<img src="'+plugin_url+'images/true.png">');
					li_adding.find('.add').removeAttr("disabled");
				}
					
			}
		});
		
	}
	

});

jQuery(document).on('click', 'li .remove-link', function(ev){
	ev.preventDefault();
	
	var point = this;
		var endpoint = jQuery(this).attr('data-endpoint');
		var r = confirm("Are You sure do this");
		
	if(r==true)
	{
		
		jQuery.ajax({
			type:'POST',
			url:phoen_myaccount_Ajax.ajax_url,
			data:{ 
			endpoint:endpoint,
			action:'phoen_remove_endpoint'
				
			},
			success:function(data){

				point.closest('li').remove();
				
				update_list_endpoint();	
				
			}
			
		});
	}
	});