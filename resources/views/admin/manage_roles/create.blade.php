@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body">
		<form class="forms-sample" method="POST" action="{{route('adminRoleManagerStore')}}" id="roleCreate">
		 @csrf
			  <div class="form-group">
				<label for="old_password">Role Name</label>
				<input type="text" class="form-control" name="name" value="{{old('name')}}" placeholder="Role name" required />
				@error('name')
					<span class="invalid-feedback" role="alert" style="display:block;">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			  </div>
			  <button type="submit" class="btn btn-gradient-primary mr-2">Assign Access Rights</button>
			  <button type="reset" class="btn btn-light">Reset</button>
		</form>
	  </div>
	</div>
</div>
</div>
@section('script')
{!! JsValidator::formRequest('App\Http\Requests\Admin\AddRoleRequest','#roleCreate') !!}
@endsection
@endsection