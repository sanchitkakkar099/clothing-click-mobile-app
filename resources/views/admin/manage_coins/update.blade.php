@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" method="POST" action="{{route('adminCoinsUpdate')}}" id="userUpdate" enctype="multipart/form-data">
                    @csrf
                    
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
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" name="photo" placeholder="Photo" />
                         @if(isset($user->photo) && $user->photo != '')
                        <img src="{{ResizeImage($user->photo,32,32)}}" class="mt-10" />
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="name">Price</label>
                        <input type="text" class="form-control" name="price" value="{{$user->price}}" placeholder="price" required />
                        @error('price')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="coins">Coins</label>
                        <input type="text" class="form-control" name="coins" value="{{$user->coins}}" placeholder="Coins"/>
                    
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateAdminCoinsRequest','#userUpdate') !!}
@endsection
@endsection