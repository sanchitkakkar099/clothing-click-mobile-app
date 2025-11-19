@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body">
		@permission('user_profile-update')
		<form class="forms-sample" method="POST" action="{{route('adminUserProfileUpdate')}}" id="profileUpdate" enctype="multipart/form-data" >
		 @csrf
		 @endpermission
		
			  <div class="form-group">
				<label for="name">Name</label>
				<input type="text" class="form-control" name="name" value="{{Auth::guard('admin')->user()->name}}" placeholder="Name" required />
				@error('name')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  <div class="form-group">
				<label for="email">Email</label>
				<input type="email" class="form-control" name="email" value="{{Auth::guard('admin')->user()->email}}" placeholder="Email" required />
				@error('email')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  <div class="form-group">
				<label for="photo">Photo</label>
				<input type="file" class="form-control" name="photo" placeholder="Photo" />
				
				<img src="{{ResizeImage(Auth::guard('admin')->user()->image_id,100,100)}}" class="mt-10" />
				@error('photo')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  @permission('user_profile-update')
			  <button type="submit" class="btn btn-gradient-primary mr-2">Update</button>
			  <button type="reset" class="btn btn-light">Cancel</button>
			  @endpermission
		</form>
	  </div>
	</div>
</div>
</div>
@section('script')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateUserProfileRequest','#profileUpdate') !!}
@endsection
@endsection