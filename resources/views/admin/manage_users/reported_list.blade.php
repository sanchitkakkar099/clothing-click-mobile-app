@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body"> 
	  @if(count($users) > 0)
		 <form class="form-inline float-right" action="{{route('adminUserManager')}}" method="GET">
		  <input type="text" value="{{isset($filter['search'])?$filter['search']:''}}" name="search" class="form-control mb-2 mr-sm-2" placeholder="Search" />
		  <button type="submit" title="Filter" class="btn-sm btn-gradient-primary mb-2 mr-2"><i class="mdi mdi-filter"></i></button>
		   <button type="reset" onclick="location.href='{{route('adminUserManager')}}'" title="Reset Filter" class="btn-sm btn-gradient-danger mb-2 mr-2"><i class="mdi mdi-refresh"></i></button>
		   @permission('admin_users-create')
			<button type="button" onclick="location.href='{{route('adminUserManagerCreate')}}'" title="Add User" class="btn-sm btn-gradient-primary mb-2">Add User</button>
			@endpermission
		</form>
		<div class="clearfix"></div>
		<div class="table-horizontal-scroll">		
				<table class="table table-bordered">
				  <thead>
					<tr>
					  <th> Photo </th>
					  <th> Name </th>
					  <th> Email </th>
					  <th> Reported  </th>
					  <th> Action </th>
					</tr>
				  </thead>
				  <tbody>
					@foreach($users as $user)
					<tr>
                        @php
						if(isset($user['user_details']->image_id) && $user['user_details']->image_id != ''){
						@endphp
					  <td> <img src="{{ResizeImage($user['user_details']->image_id,50,50)}}" /> </td>
                        @php
                            } else {
                        @endphp
                      <td> - </td>
					  @php
					  }
					  @endphp
					  <td> {{$user['user_details']->name}} </td>
					  <td> {{$user['user_details']->email}} </td>
					  <td>
					  {{$user['reporting_details']->total}}
					  </td>
					  <td><a href="{{route('adminUserReporting',$user['user_details']->id)}}">View</a>
					  </td>
					  
					</tr>
					@endforeach
				  </tbody>
				</table>
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
		var msg = "Once deleted, you will not be able to recover this user";
		deleteWarning(msg,$(this).attr('href'));
	});
	
	$('body').on('click','.switch_publish',function(e){
		var url = '{{route("adminUserManagerPublish")}}';
		var value = $(this).val();
		publishStatus(value,url);
	});
});
</script>
@endsection