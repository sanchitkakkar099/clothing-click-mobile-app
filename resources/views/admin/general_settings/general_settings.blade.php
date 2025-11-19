@extends('admin.layouts.app')
@section('content')
<div class="row">
<div class="col-12">
	<div class="card">
	  <div class="card-body">
		<h4 class="card-title">Manage General Settings</h4>
		<form class="form-sample" method="GET" action="{{route('adminGeneralSettings')}}">
		  <p class="card-description"></p>
		  <div class="row">
			<div class="col-md-6">
			  <div class="form-group row">
				<label class="col-sm-3 col-form-label">Select Category</label>
				<div class="col-sm-9">
				  <select class="form-control" name="category">
					<option value="">Select Category</option>
					@foreach($categories as $category)
						<option value="{{$category->setting_group}}" {{isset($_REQUEST['category']) && $_REQUEST['category'] == $category->setting_group ? 'selected' : ''}}>{{ucwords($category->setting_group)}}</option>
					@endforeach
				  </select>
				</div>
			  </div>
			</div>
			<div class="col-md-6">
			  <button type="submit" class="btn btn-gradient-primary mb-2">View Settings</button>
			</div>
		  </div>
		</form>
	  </div>
	</div>
</div>
@if(isset($settings) && count($settings) > 0)
<div class="col-12 grid-margin stretch-card mt-10">
	<div class="card">
	  <div class="card-body">
		@permission('general_settings-update')
		<form class="forms-sample" method="POST" action="{{route('adminUpdateGeneralSettings')}}" enctype="multipart/form-data">
		 @csrf
		 @endpermission
		 @php $group = ''; @endphp
		 @foreach($settings as $setting)
			 @php 
				$require = '';
				if($setting->is_required == 1){
					$require = 'required';
				}
			 @endphp
			 
			  @if($group == '' || $setting->setting_group != $group)
				  @php $group = $setting->setting_group; @endphp
				  <h4 class="card-title">{{$setting->setting_group}} </h4>
			  @endif
			  
			  <div class="form-group">
			  	<div class="row">
			  		<div class="{{(in_array($setting->name_slug, $imageOption))?'col-md-6':'col-md-12'}}">
						<label for="{{$setting->id}}">{{$setting->name}}</label>
						<input type="text" class="form-control" name="general_settings[{{$setting->id}}]" value="{{$setting->value}}" placeholder="{{$setting->name}}" {{$require}} />
			  		</div>
						@if(in_array($setting->name_slug, $imageOption))
						<div class="col-md-4">
							<label>{{$setting->name}} Image </label>
							<input type="file" name="images[{{$setting->id}}]" class="form-control">
						</div>
						<div class="col-md-2">
							<br>
							<label>&nbsp; </label>
							@if($setting->photo !=null)
							<a href="{{ResizeImage($setting->photo,80,80)}}" target="_blank">
							<img src="{{ResizeImage($setting->photo,80,80)}}" /></a>
							@endif
						</div>						
						@endif
				</div>
			  </div>
			  @endforeach
			  <div class="row">
				<div class="col-md-6">
				  <div class="form-group">
					<div class="form-check">
					  <label class="form-check-label">
						<input type="checkbox" class="form-check-input" name="cached[]" value="cache"> Clear Cache <i class="input-helper"></i></label>
					</div>
				  </div>
				  <div class="form-group">
					<div class="form-check">
					  <label class="form-check-label">
						<input type="checkbox" class="form-check-input" name="cached[]" value="config"> Clear Configuration Cache <i class="input-helper"></i></label>
					</div>
				  </div>
				</div>
				<div class="col-md-6">
				  <div class="form-group">
					<div class="form-check">
					  <label class="form-check-label">
						<input type="checkbox" class="form-check-input" name="cached[]" value="view"> Clear Compiled View <i class="input-helper"></i></label>
					</div>
				  </div>
				  <div class="form-group">
					<div class="form-check">
					  <label class="form-check-label">
						<input type="checkbox" class="form-check-input" name="cached[]" value="route"> Clear Route Cache <i class="input-helper"></i></label>
					</div>
				  </div>
				</div>
			  </div>
			  @permission('general_settings-update')
			  <button type="submit" class="btn btn-gradient-primary mr-2">Update</button>
			  <button type="reset" class="btn btn-light">Cancel</button>
			  @endpermission
		</form>
	  </div>
	</div>
</div>
@endif
</div>
@endsection