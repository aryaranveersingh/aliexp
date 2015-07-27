
/* 
* Function to add and display Loader....
*/

(function ($, window, document, undefined) {
	$.fn.somefunction = function(parameters){
			return $('.dimmer').prepend('<div id="preloader"><div class="loader"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
}
})(jQuery, window , document);

$('body').append('<div class="dimmer" />');
$('body .dimmer').somefunction('Please Wait...');


window.setTimeout(function(){
	$('body .dimmer').remove();
},1000);

$(document).ready(function(){
	$(this).loadStores();
});

$(document).on('click','.btn-warning',function(){
	$(this).editStore();
});

$(document).on('click','.updatestore',function(){
	$(this).updatestore();
});

$(document).on('click','.btn-danger',function(){
	$(this).deletestore();
});

$.fn.updatestore = function(){
	var formdata = $('.editform').serializeArray();

	submit = true;
	$.each(formdata,function(a,b){
		if(b.value == ''){
			$('input[name="'+b.name+'"]').parent().addClass('has-error');
			submit = false;
		}
	})

	if(submit){

		$('.editform').find('div').removeClass('has-error');
		formdata = JSON.stringify(formdata);
		$.ajax({
			url:'api/updatestore',
			type:'POST',
			data:{store:formdata,storeid:$(this).attr('attrid')},
			success:function(a){
				console.log(a);
				$.stores = a.data;
				$('#storeform').parent().dataTable().fnDestroy();
				$('#storeform').empty();
				$.stores = a.data;
				data = a.data;
				$.each(data,function(a,b){
					var tr = "<tr><td>"+b.storeid+"</td><td>"+b.storename+"</td>"+"<td>"+b.storeurl+"</td><td>"+b.ordercount+"</td><td>"+b.up_product+"</td><td>"+b.timesync+"</td><td><button class=\"btn btn-warning btn-sm\" attrid='"+b.storeid+"'>Edit</button><span class='colspan'></span><button class=\"btn btn-danger btn-sm\" attrid='"+b.storeid+"'>Delete</button></td></tr>";
					$('#storeform').append(tr);
				});

				dataTableObjects = $('#storeform').parent().dataTable({
	              "order": [[ 0, "desc" ]],
	              "bDestroy": true,
	              "bLengthChange": false,
	              "searching": true,
	              "bFilter": false
	            });
				alert("Store details has been updated.");
			},
			error:function(a,b){
				console.log(a);
			}
		});

		
	}
}

$.fn.deletestore = function(){

		$.ajax({
			url:'api/deletestore',
			type:'POST',
			data:{storeid:$(this).attr('attrid')},
			success:function(a){
				console.log(a);
				$.stores = a.data;
				$('#storeform').parent().dataTable().fnDestroy();
				$('#storeform').empty();
				$.stores = a.data;
				data = a.data;
				$.each(data,function(a,b){
					var tr = "<tr><td>"+b.storeid+"</td><td>"+b.storename+"</td>"+"<td>"+b.storeurl+"</td><td>"+b.ordercount+"</td><td>"+b.up_product+"</td><td>"+b.timesync+"</td><td><button class=\"btn btn-warning btn-sm\" attrid='"+b.storeid+"'>Edit</button><span class='colspan'></span><button class=\"btn btn-danger btn-sm\" attrid='"+b.storeid+"'>Delete</button></td></tr>";
					$('#storeform').append(tr);
				});

				dataTableObjects = $('#storeform').parent().dataTable({
	              "order": [[ 0, "desc" ]],
	              "bDestroy": true,
	              "bLengthChange": false,
	              "searching": true,
	              "bFilter": false
	            });
				alert("Store was deleted.");
			},
			error:function(a,b){
				console.log(a);
			}
		});
}

$.fn.loadStores = function(){

	$.ajax({
		url:'api/loadStores',
		type:'GET',
		success:function(a){
			console.log(a);
			$.stores = a.data;
			data = a.data;
			$.each(data,function(a,b){
				var tr = "<tr><td>"+b.storeid+"</td><td>"+b.storename+"</td>"+"<td>"+b.storeurl+"</td><td>"+b.ordercount+"</td><td>"+b.up_product+"</td><td>"+b.timesync+"</td><td><button class=\"btn btn-warning btn-sm\" attrid='"+b.storeid+"'>Edit</button><span class='colspan'></span><button class=\"btn btn-danger btn-sm\" attrid='"+b.storeid+"'>Delete</button></td></tr>";
				$('#storeform').append(tr);
			});

			$.dataTableObjects = $('#storeform').parent().dataTable({
              "order": [[ 0, "desc" ]],
              "bDestroy": true,
              "bLengthChange": false,
              "searching": true,
              "bFilter": false
            });
		}
	});

}

$.fn.addtostore = function(){
	var formdata = $('.addtostoreform').serializeArray();

	submit = true;
	$.each(formdata,function(a,b){
		if(b.value == ''){
			$('input[name="'+b.name+'"]').parent().addClass('has-error');
			submit = false;
		}
	})

	if(submit){

		$('.addtostoreform').find('div').removeClass('has-error');
		formdata = JSON.stringify(formdata);
		$.ajax({
			url:'api/addStore',
			type:'POST',
			data:{store:formdata},
			success:function(a){
				console.log(a);
				$('#storeform').parent().dataTable().fnDestroy();
				$('#storeform').empty();
				$.stores = a.data;
				data = a.data;
				$.each(data,function(a,b){
					var tr = "<tr><td>"+b.storeid+"</td><td>"+b.storename+"</td>"+"<td>"+b.storeurl+"</td><td>"+b.ordercount+"</td><td>"+b.up_product+"</td><td>"+b.timesync+"</td><td><button class=\"btn btn-warning btn-sm\" attrid='"+b.storeid+"'>Edit</button><span class='colspan'></span><button class=\"btn btn-danger btn-sm\" attrid='"+b.storeid+"'>Delete</button></td></tr>";
					$('#storeform').append(tr);
				});

				dataTableObjects = $('#storeform').parent().dataTable({
	              "order": [[ 0, "desc" ]],
	              "bDestroy": true,
	              "bLengthChange": false,
	              "searching": true,
	              "bFilter": false
	            });
				alert("New store has benn added");
			},
			error:function(a,b){
				console.log(a);
			}
		});

		
	}

}




$.fn.getStoreSettings = function(sid){
	returninfo = false;
	$.each($.stores,function(a,b){
		if(parseInt(b.storeid) == parseInt(sid)){
			returninfo = b;
		}
		else{
			console.log(sid);
		}
	});
	return returninfo;
}

$.fn.editStore = function(){
		console.log($(this).attr('attrid'));
		var infostore = $(this).getStoreSettings($(this).attr('attrid'));
		console.log(infostore);
    	var template =    $('<div class="ui modal large"/>');
    	$(template).append ('<div class="modal-dialog">'+
							    '<div class="modal-content">'+
							      '<div class="modal-header">'+
							        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
							        '<h4 class="modal-title">Edit Store</h4>'+
							      '</div>'+
							      '<div class="modal-body">'+
							        '<form class="form editform">'+
							        	'<div class="form-group">'+
							        		'<label>Store URL</label><input class="form-control storeurl" name="storeurl" />'+
							        	'</div>'+
							        	'<div class="form-group">'+
							        		'<label>Store Name</label><input class="form-control storename" name="storename"/>'+
							        	'</div>'+
							        	'<div class="form-group">'+
							        		'<label>Minimum Order</label><input class="form-control smallorder" name="smallorder"/>'+
							        	'</div>'+
							        	'<div class="form-group">'+
							        		'<label>Up Product</label><input class="form-control uporder" name="uporder"/>'+
							        	'</div>'+
							        	'<div class="form-group">'+
							        		'<label>Time Sync</label><input class="form-control timsync" name="timsync"/>'+
							        	'</div>'+
							        	
							        '</form>'+
							      '</div>'+
							      '<div class="modal-footer">'+
							        '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'+
							        '<button type="button" class="btn btn-primary updatestore" attrid="'+$(this).attr('attrid')+'">Save changes</button>'+
							      '</div>'+
							    '</div>'+
							  '</div>'+
							'</div>');
		$('body').append(template);
		$(template).modal('show');
		$(template).on('hidden.bs.modal', function (e) {
		  $(this).empty().remove();
		})
		$(template).find('.storeurl').val(infostore.storeurl);
		$(template).find('.storename').val(infostore.storename);
		$(template).find('.smallorder').val(infostore.ordercount);
		$(template).find('.uporder').val(infostore.up_product);
		$(template).find('.timsync').val(infostore.timesync);
}