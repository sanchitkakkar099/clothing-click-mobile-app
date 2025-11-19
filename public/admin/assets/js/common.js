$(function () {
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
		}
	});
});
	
function deleteWarning(msg,action)
{
	swal({
	  title: "Are you sure?",
	  text: msg,
	  icon: "warning",
	  buttons: true,
	  dangerMode: true,
	})
	.then((willDelete) => {
	  if (willDelete) {
		window.location = action;
	  }
	});
}

function publishStatus(id,url,msg = false)
{
	if(!msg){
		return ajaxPublish(id,url);
	}else{
		swal({
		  title: "Are you sure?",
		  text: msg,
		  icon: "warning",
		  buttons: true,
		  dangerMode: true,
		})
		.then((willDelete) => {
		  if (willDelete) {
			return ajaxPublish(id,url);
		  }else{
			  location.reload();
		  }
		});
	}
}

function ajaxPublish(id,url)
{
	$.ajax({
		url: url,
		type: 'POST',
		data: {id:id},
		dataType: 'JSON',
		success: function (data) { 
			console.log(data);
		}
	}); 
}