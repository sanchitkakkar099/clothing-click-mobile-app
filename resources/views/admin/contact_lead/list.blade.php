@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body"> 
	  @if(count($leads) > 0)
	  <form class="form-inline float-right" action="{{route('adminContactLeads')}}" method="GET">
		  <input type="text" value="{{isset($filter['search'])?$filter['search']:''}}" name="search" class="form-control mb-2 mr-sm-2" placeholder="Search" />
		  <button type="submit" title="Filter" class="btn-sm btn-gradient-primary mb-2 mr-2"><i class="mdi mdi-filter"></i></button>
		   <button type="reset" onclick="location.href='{{route('adminContactLeads')}}'" title="Reset Filter" class="btn-sm btn-gradient-danger mb-2"><i class="mdi mdi-refresh"></i></button>
		</form>
		<div class="clearfix"></div>
		<div class="table-horizontal-scroll">		
				<table class="table table-bordered">
				  <thead>
					<tr>
					  <th> Name </th>
					  <th> Subject </th>
					  <th> Detail </th>
					  <th> Date & Time </th>
					  <th> Action </th>
					</tr>
				  </thead>
				  <tbody>
					@foreach($leads as $lead)
					<tr>
					  <td> {{$lead->name}} </td>
					  <td> {{$lead->subject}} </td>
					  <td> 
						<p><a href="#detail{{$lead->id}}" data-toggle="modal" data-target="#detail{{$lead->id}}">{{$lead->subject}}</a></p>
						  <div class="modal fade" id="detail{{$lead->id}}" role="dialog">
							<div class="modal-dialog">
							  <div class="modal-content">
								<div class="modal-header">
								  <h4 class="modal-title">Details</h4>
								  <button type="button" class="close" data-dismiss="modal">&times;</button>
								</div>
								<div class="modal-body">
								  <p><b>Email: </b><a href="mailto:{{$lead->email}}">{{$lead->email}}</a></p>
								  <p><b>Phone: </b><a href="tel:{{$lead->phone}}">{{$lead->phone}}</a></p>
								  <p><b>Message: </b> {{$lead->message}} </p>
								</div>
							  </div>
							</div>
						  </div>
					  </td>
					  <td> {{$lead->created_at}} </td>
					  <td> 
						@permission('contact_leads-delete')
							<a href="{{route('adminContactLeadsDelete',$lead->id)}}" class="delete"><button type="button" title="Delete" class="btn-sm btn-gradient-danger btn-fw"><i class="mdi mdi-delete"></i></button></a>
						@endpermission
					  </td>
					</tr>
					@endforeach
				  </tbody>
				</table>
			</div>
			<div class="mt-2">
				<div class="float-right">
					{{$leads->appends($_REQUEST)->links()}}
				</div>
				<div class="float-left">
					@include('admin.layouts.show_records_per_page')
				</div>
			</div>
			@else
				<div class="alert alert-danger" role="alert">
				  No data found.
				</div>
			@endif
	  </div>
	</div>
</div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function(){
	$('body').on('click','.delete',function(e){
		e.preventDefault();
		var msg = "Once deleted, you will not be able to recover this conatct lead!";
		deleteWarning(msg,$(this).attr('href'));
	});
});
</script>
@endsection