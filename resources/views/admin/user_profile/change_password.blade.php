@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body">
		@permission('change_password-update')
		<form class="forms-sample" method="POST" action="{{route('adminUpdatePassword')}}" id="passwordUpdate">
		 @csrf
		 @endpermission
			  <div class="form-group">
				<label for="old_password">Old Password</label>
				<input type="password" class="form-control" name="old_password" value="" placeholder="Old Password" required />
				@error('old_password')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  <div class="form-group">
				<label for="password">New Password</label>
				<input type="password" class="form-control" name="password" value="" placeholder="New Password" required />
				@error('password')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  <div class="form-group">
				<label for="password_confirmation">Confirm New Password</label>
				<input type="password" class="form-control" name="password_confirmation" placeholder="Confirm New Password" />
				@error('password_confirmation')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  @permission('change_password-update')
			  <button type="submit" class="btn btn-gradient-primary mr-2">Update</button>
			  <button type="reset" class="btn btn-light">Cancel</button>
			  @endpermission
		</form>
	  </div>
	</div>
</div>
</div>
@section('script')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdatePasswordRequest','#passwordUpdate') !!}
@endsection
@endsection