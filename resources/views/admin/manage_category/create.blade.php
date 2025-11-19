@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" method="POST" action="{{route('adminCategoryStore')}}" id="userCreate" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Select Gender</label>
                        <select class="form-control" name="gender_id">
                            <option value="">Select Gender</option>
                            @foreach($genderdata as $data)

                            <option value="{{$data->id}}">{{ucwords($data->name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Sub Category</label>
                        <select class="form-control" name="parent_id">
                            <option value="0">Sub Category</option>
                            @foreach($subcatdata as $sdata)
                            @php
                            $genderdata = App\gender::getGender($sdata->gender_id);
                            @endphp
                            <option value="{{$sdata->id}}">{{ucwords($sdata->name)}} - {{ $genderdata->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" value="{{old('name')}}" maxlength="25" placeholder="name" required />
                        @error('name')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" name="photo" placeholder="Photo" />
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon</label>
                        <input type="file" class="form-control" name="icon" placeholder="icon" />
                    </div>
                    <button type="submit" class="btn btn-gradient-primary mr-2">Add</button>
                    <button type="reset" class="btn btn-light">Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>
@section('script')
{!! JsValidator::formRequest('App\Http\Requests\Admin\AddAdminCategoryRequest','#userCreate') !!}
@endsection
@endsection