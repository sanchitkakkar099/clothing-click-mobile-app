@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body">
		<form class="forms-sample" method="POST" action="{{route('adminUserManagerUpdate')}}" id="userUpdate">
		 @csrf
				<div class="form-group">
				  <label for="role_id">Select Role</label>
				  <select class="form-control" name="role_id">
					<option value="">Select Role</option>
					@foreach($roles as $value)
					<option value="{{base64_encode($value->id)}}") {{$value->id == $user->role_id ? 'selected': '' }} >{{ucwords($value->name)}}</option>
					@endforeach
				  </select>
				</div>
			  <div class="form-group">
				<label for="name">Name</label>
				<input type="text" class="form-control" name="name" value="{{$user->name}}" placeholder="name" required />
				@error('name')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  <div class="form-group">
				<label for="email">Email</label>
				<input type="email" class="form-control" name="email" value="{{$user->email}}" placeholder="email" required />
				@error('email')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  <div class="form-group">
				<label for="password">Password</label>
				<input type="password" class="form-control" name="password" value="" placeholder="password" />
				@error('password')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			   <div class="form-group">
				<label for="password_confirmation">Confirm Password</label>
				<input type="password" class="form-control" name="password_confirmation" value="" placeholder="confirm password" />
				@error('password_confirmation')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  <input type="hidden" name="uid" value="{{$user->id}}" />
			  <button type="submit" class="btn btn-gradient-primary mr-2">Update</button>
			  <button type="reset" class="btn btn-light">Reset</button>
		</form>
	  </div>
	</div>
</div>
</div>
@section('script')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateAdminUserRequest','#userUpdate') !!}
@endsection
@endsection