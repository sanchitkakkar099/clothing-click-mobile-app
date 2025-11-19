@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" method="POST" action="{{route('adminSizeStore')}}" id="userCreate">
                    @csrf
                    <div class="form-group">
                        <label for="name">Select Category</label>
                        <select class="form-control" name="category_id">
                            <option value="">Select Category</option>
                            @foreach($categorydata as $data)
                            @php
                            $genderdata = App\gender::getGender($data->gender_id);
                            @endphp
                            <option value="{{$data->id}}">{{ucwords($data->name)}} - {{ $genderdata->name }}</option>
                            @endforeach
                        </select>
                    </div>	
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" value="{{old('name')}}" placeholder="name" required />
                        @error('name')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-gradient-primary mr-2">Add</button>
                    <button type="reset" class="btn btn-light">Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>
@section('script')
{!! JsValidator::formRequest('App\Http\Requests\Admin\AddAdminSizeRequest','#userCreate') !!}
@endsection
@endsection