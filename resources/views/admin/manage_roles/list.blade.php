@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body"> 
	  @if(count($roles) > 0)
		@permission('admin_roles-create')
		<button type="button" onclick="location.href='{{route('adminRoleManagerCreate')}}'" title="Add Role" class="btn-sm btn-gradient-primary mb-2 float-right">Add Role</button>
		@endpermission
		<div class="clearfix"></div>
		<div class="table-horizontal-scroll">		
				<table class="table table-bordered">
				  <thead>
					<tr>
					  <th> Role </th>
					  <th> Status </th>
					  <th> Action </th>
					</tr>
				  </thead>
				  <tbody>
					@php $ignore = [1,2]; @endphp
					@foreach($roles as $role)
					<tr>
					  <td> {{$role->name}} </td>
					  <td>
					@if(!in_array($role->id,$ignore))			@include('admin.layouts.publish_switch',['value'=>$role->id,'checked'=>$role->is_active])
						@else
							-
						@endif
					  </td>
					  <td>
						@permission('admin_roles-update')
							<a href="{{route('adminRoleManagerEdit',$role->id)}}"><button type="button" title="Update" class="btn-sm btn-gradient-success btn-fw"><i class="mdi mdi-border-color"></i></button></a>
						@endpermission
						@permission('access_rights-update')
							<a href="{{route('adminAccessRights')}}?role={{base64_encode($role->id)}}"><button type="button" title="Update Access Rights" class="btn-sm btn-gradient-primary btn-fw"><i class="mdi mdi-access-point"></i></button></a>
						@endpermission
						@if(!in_array($role->id,$ignore))
							@permission('admin_roles-delete')
								<a href="{{route('adminRoleManagerDelete',$role->id)}}" class="delete"><button type="button" title="Delete" class="btn-sm btn-gradient-danger btn-fw"><i class="mdi mdi-delete"></i></button></a>
							@endpermission
						@endif
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
		var msg = "Once deleted, you will not be able to recover this role and all users will deleted who are belongs to this role!";
		deleteWarning(msg,$(this).attr('href'));
	});
	
	$('body').on('click','.switch_publish',function(e){
		var url = '{{route("adminRoleManagerPublish")}}';
		var value = $(this).val();
		
		if($(this).prop('checked')){
			publishStatus(value,url);
		}else{
			var msg = "Once de-activated, all users will also de-activated who are belongs to this role!";
			publishStatus(value,url,msg);
		}
	});
});
</script>
@endsection