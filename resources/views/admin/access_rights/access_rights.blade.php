@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12">
	<div class="card">
	  <div class="card-body">
		<h4 class="card-title">Manage Access Rights</h4>
		<form class="form-sample" method="GET" action="{{route('adminAccessRights')}}">
		  <p class="card-description"></p>
		  <div class="row">
			<div class="col-md-6">
			  <div class="form-group row">
				<label class="col-sm-3 col-form-label">Select Role</label>
				<div class="col-sm-9">
				  <select class="form-control" name="role">
					<option value="">Select Role</option>
					@foreach($roles as $value)
						<option value="{{base64_encode($value->id)}}" @if(isset($role) && $role->id == $value->id) selected @endif)>{{ucwords($value->name)}}</option>
					@endforeach
				  </select>
				</div>
			  </div>
			</div>
			<div class="col-md-6">
			  <button type="submit" class="btn btn-gradient-primary mb-2">View Access Rights</button>
			</div>
		  </div>
		</form>
	  </div>
	</div>
</div>

@if(isset($role) && !empty($role))
<div class="col-12 mt-10">
	<div class="card">
	  <div class="card-body">
		<h4 class="card-title">Access Rights For {{$role->name}}</h4>
		@permission('access_rights-update')
		<form method="post" action="{{route('adminUpdateAccessRights')}}">
		@csrf
		@endpermission
		<div class="table-horizontal-scroll">
		<table class="table table-bordered">
		  <thead>
			<tr>
			  <th> Module </th>
			  <th> List </th>
			  <th> View/Detail </th>
			  <th> Create </th>
			  <th> Update </th>
			  <th> Publish/Unpublish </th>
			  <th> Delete </th>
			</tr>
		  </thead>
		  <tbody>
			@foreach($modules as $module)
			<tr>
			  <td> {{ucwords($module->name)}} </td>
			  @php 
				$permissions = $module->permissions;
				$modulePermissions = [];
				$givenPermission = [];
				foreach($permissions as $permission){
					array_push($modulePermissions,strtolower($permission->per_slug));
					$modulePermission = $permission->role_permission()->where([['role_id',$role->id],['permission_id',$permission->id]])->count();
					if($modulePermission > 0){
						array_push($givenPermission,strtolower($permission->per_slug));
					}
				}
			  @endphp
			  <td>
				@if(strpos(implode(',',$modulePermissions), 'list'))
					<div class="form-check mt-0">
					  <label class="form-check-label">
						<input type="checkbox" name="rights[]" value="{{$module->id}},list" class="form-check-input" @if(strpos(implode(',',$givenPermission),'list')) checked @endif /><i class="input-helper"></i></label>
					</div>
				@endif
			  </td>
			  <td>
			  @if(strpos(implode(',',$modulePermissions), 'view'))
					<div class="form-check mt-0">
					  <label class="form-check-label">
						<input type="checkbox" name="rights[]" value="{{$module->id}},view" class="form-check-input" @if(strpos(implode(',',$givenPermission),'view')) checked @endif /><i class="input-helper"></i></label>
					</div>
			  @endif
			  </td>
			  <td>
			   @if(strpos(implode(',',$modulePermissions), 'create'))
					<div class="form-check mt-0">
					  <label class="form-check-label">
						<input type="checkbox" name="rights[]" value="{{$module->id}},create" class="form-check-input" @if(strpos(implode(',',$givenPermission),'create')) checked @endif /><i class="input-helper"></i></label>
					</div>
				@endif
			  </td>
			  <td>
			   @if(strpos(implode(',',$modulePermissions), 'update'))
					<div class="form-check mt-0">
					  <label class="form-check-label">
						<input type="checkbox" name="rights[]" value="{{$module->id}},update" class="form-check-input" @if(strpos(implode(',',$givenPermission),'update')) checked @endif /><i class="input-helper"></i></label>
					</div>
				@endif
			  </td>
			  <td>
			   @if(strpos(implode(',',$modulePermissions), 'publish'))
					<div class="form-check mt-0">
					  <label class="form-check-label">
						<input type="checkbox" name="rights[]" value="{{$module->id}},publish" class="form-check-input" @if(strpos(implode(',',$givenPermission),'publish')) checked @endif /><i class="input-helper"></i></label>
					</div>
				@endif
			  </td>
			  <td>
			  @if(strpos(implode(',',$modulePermissions), 'delete'))
					<div class="form-check mt-0">
					  <label class="form-check-label">
						<input type="checkbox" name="rights[]" value="{{$module->id}},delete" class="form-check-input" @if(strpos(implode(',',$givenPermission),'delete')) checked @endif><i class="input-helper"></i></label>
					</div>
				@endif
			  </td>
			</tr>
			@endforeach
		  </tbody>
		</table>
		</div>
		@permission('access_rights-update')
		<input type="hidden" name="role" value="{{base64_encode($role->id)}}" />
		<div class="col-md-6 mt-20" style="padding-left:0px;padding-right:0px;">
		  <button type="submit" class="btn btn-gradient-success">Update Access Rights</button>
		</div>
		</form>
		@endpermission
	  </div>
	</div>
</div>
@endif
</div>
@endsection