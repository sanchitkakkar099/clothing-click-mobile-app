@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" method="POST" action="{{route('NotificationsUpdate')}}" id="userUpdate" enctype="multipart/form-data">
                    @csrf
                     <div class="form-group">
                        <label for="name">Select User</label>
                        <select class="form-control" name="fkuser">
                            <option value="0">All</option>
                            @foreach($usersdata as $users)
                            @php
                            if($users->id == $user->fkuser){
                            $selected = 'selected="selected"';
                            }else{
                            $selected = '';
                            }
                            @endphp
                            <option value="{{$users->id}}" {{$selected}}>{{ucwords($users->name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Subject <span class="req">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{$user->name}}" placeholder="Subject" required />
                        @error('name')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Description <span class="req">*</span></label>
                        <textarea class="form-control" name="description" placeholder="description" rows="5" required>{{$user->description}}</textarea>
                        @error('description')
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateNotificationsRequest','#userUpdate') !!}
@endsection
@endsection