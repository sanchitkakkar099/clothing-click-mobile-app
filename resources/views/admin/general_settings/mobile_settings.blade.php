@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12">
	<div class="card">
	  <div class="card-body">
		<h4 class="card-title">Manage Mobile Settings</h4>
	  </div>
	</div>
</div>
<div class="col-12 grid-margin stretch-card mt-10">
	<div class="card">
	  <div class="card-body">
		<form class="forms-sample" method="POST" action="{{route('adminMobileAppSettings')}}" enctype="multipart/form-data">
		 @csrf
		 <h3>Landing Page Settings</h3>
		 <div class="form-group">
            <label for="name">Type</label>
            <select name="type" class="form-control" required>
				<option value="">Select </option>
				<option value="image" @if($settings->type == 'image') {{'selected'}} @endif>Image </option>
				<option value="video" @if($settings->type == 'video') {{'selected'}} @endif>Video </option>
			</select>
         </div>
		 <div class="form-group">

            <label for="photo">Upload Image/Video</label>

				<input type="file" class="form-control" name="file" placeholder="" />

				 @if(isset($settings->url) && $settings->url != '' && $settings->type == 'image')

				<img src="{{$file}}" class="mt-10" />
				@else
					<video width="320" height="240" controls>
					  <source src="{{$file}}" >
					  Your browser does not support the video tag.
					</video>
				@endif

				 @error('photo')

				<span class="invalid-feedback" role="alert" style="display:block;">

					<strong>{{ $message }}</strong>

				</span>

				@enderror

			</div>
			  
			  <button type="submit" name="update" class="btn btn-gradient-primary mr-2">Update</button>
			  <button type="reset" class="btn btn-light">Cancel</button>
		</form>
	  </div>
	</div>
</div>
</div>
@endsection