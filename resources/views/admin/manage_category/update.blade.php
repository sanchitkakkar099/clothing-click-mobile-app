@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" method="POST" action="{{route('adminCategoryUpdate')}}" id="userUpdate" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Select Gender</label>
                        <select class="form-control" name="gender_id">
                            <option value="">Select Gender</option>
                            @foreach($genderdata as $data)
                            @php
                            if($data->id == $user->gender_id){
                            $selected = 'selected="selected"';
                            }else{
                            $selected = '';
                            }
                            @endphp
                            <option value="{{$data->id}}" {{$selected}}>{{ucwords($data->name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Sub Category</label>
                        <select class="form-control" name="parent_id">
                            <option value="0">Sub Category</option>
                            @foreach($subcatdata as $sdata)
                             @php
                            if($sdata->id == $user->parent_id){
                            $selected = 'selected="selected"';
                            }else{
                            $selected = '';
                            }
                            $genderdata = App\gender::getGender($sdata->gender_id);
                            @endphp
                            <option value="{{$sdata->id}}" {{$selected}}>{{ucwords($sdata->name)}} - {{ $genderdata->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" value="{{$user->name}}" maxlength="25" placeholder="name" required />
                        @error('name')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                     <div class="form-group">
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" name="photo" placeholder="Photo" />
                         @if(isset($user->photo) && $user->photo != '')
                        <img src="{{ResizeImage($user->photo,32,32)}}" class="mt-10" />
                        @endif
                    </div>   										<div class="form-group">
                        <label for="icon">Icon</label>
                        <input type="file" class="form-control" name="icon" placeholder="Icon" />
                         @if(isset($user->icon) && $user->icon != '')
                        <img src="{{ResizeImage($user->icon,100,100)}}" class="mt-10" />
                        @endif
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateAdminCategoryRequest','#userUpdate') !!}
@endsection
@endsection