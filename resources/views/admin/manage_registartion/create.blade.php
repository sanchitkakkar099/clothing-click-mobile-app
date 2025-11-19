@extends('admin.layouts.app')
@section('css')
<style type="text/css">
    #display_city label{
        margin-right: 30px;
    }
/* This is to be able to center the content in the middle of the page; */
.check_container{
    width: 250px;
height: 0px;
margin: 10px auto;
}

div label input {margin-right: 100px;}

.cat{
  margin: 4px;
  /*background-color: #104068;*/
  border-radius: 4px;
  border: 1px solid;
  overflow: hidden;
  float: left;
}

.cat label {
  float: left; 
  line-height: 2.2em;
width: 4em;
height: 2em;
}

.cat label span {
  text-align: center;
  padding: 3px 0;
  display: block;
}

.cat label input {
  position: absolute;
  display: none;
  color: #fff !important;
}
/* selects all of the text within the input element and changes the color of the text */
/*.cat label input + span{color: #fff;}*/


/* This will declare how a selected input will look giving generic properties */
.cat input:checked + span {
    color: #ffffff;
    text-shadow: 0 0  6px rgba(0, 0, 0, 0.8);
}

 .required::after{
    content: ' *';
    color: red;
}
.c-red{
    color: red;
}
/*
This following statements selects each category individually that contains an input element that is a checkbox and is checked (or selected) and chabges the background color of the span element.
*/

.sizes input:checked + span{background-color: #F75A1B;}
.gender input:checked + span{background-color: #1BB8F7;}
/*.crime input:checked + span{background-color: #D9D65D;}
.history input:checked + span{background-color: #82D44E;}
.reality input:checked + span{background-color: #F3A4CF;}
.news input:checked + span{background-color: #8C1B1B;}
.scifi input:checked + span{background-color: #AC9BD1;}
.sports input:checked + span{background-color: #214A09;}*/
</style>
@endsection
@section('content')

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" method="POST" action="{{route('appRegistationStore')}}" id="userCreate" enctype="multipart/form-data">
                    @csrf	

                 <fieldset>
                    <legend>Personal Information:</legend>                    
                    <div class="form-group" style="padding-top:10px;">
                        <label for="name">Profile Type: Brand</label>
                        <input type="hidden" class="form-control" name="role_id" value="3" placeholder="name" />
                        <input type="hidden" name="signup_type" value="1">
                    </div>         
                    <div class="form-group">
                        <label for="photo">Image</label>
                        <input type="file" class="form-control" name="photo" placeholder="Photo" />
                         @error('photo')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name" class="required">Email</label>
                        <input type="text" class="form-control" name="email" value="{{old('email')}}" placeholder="email" required />
                        @error('email')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="name" class="required">Name</label>
                        <input type="text" class="form-control" name="name" value="{{old('name')}}" placeholder="name" required />
                        @error('name')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>                    
                    <div class="form-group">
                        <label for="name" class="required">Username (Display Name)</label>
                        <input type="text" class="form-control" name="username" value="{{old('username')}}" placeholder="username" required />

                        @error('username')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>                                        
                    <div class="form-group">
                        <label for="name" class="required">Password</label>
                        <input type="password" class="form-control" name="password" value="{{old('password')}}" placeholder="password" required />
                        <small style="color: darkgray;">At least 8 characters -1 uppercase -1 number</small>
                        @error('password')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="name" class="required">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation" value="{{old('password')}}" placeholder="confirm password " required />
                        @error('password_confirmation')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>      
                    <div class="form-group">
                        <label for="address_address" class="required">Select Location</label>
                        <input type="text" id="address-input" name="address_address" class="form-control map-input">
                        <input type="hidden" name="address_latitude" id="address-latitude" value="0" />
                        <input type="hidden" name="address_longitude" id="address-longitude" value="0" />
                    </div>
                    <div id="address-map-container" style="width:100%;height:400px;display:none;">
                        <div style="width: 100%; height: 100%" id="address-map" ></div>
                    </div>      
                    <div class="form-group">
                        <label for="address_address" >Add City</label>   <Br>           
                        <div id="display_city" style="padding-top:10px;"></div>  
                        @error('location')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror 
                        @error('city')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror 
                        @if(isset($location_errors) && $location_errors !=null)
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $location_errors }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">

                        <div class="row">
                            <div class="col-sm-2">
                        <label for="phone" class="required">Country Prefix</label>
                                <select name="country_code_prefix" class="form-control">
                                    @foreach(config('country_list') as $code => $country)
                                    <option value="{{$code}}_{{$country['code']}}" {{($code == 'CA')?'selected':''}}>
                                        {{$country['name']}} +{{$country['code']}} 
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-10">
                        <label for="phone" class="required">Phone</label>                                
                            <input type="text" class="form-control" name="phone" value="{{old('phone')}}" placeholder="phone" required />
                            @error('phone')
                            </div>
                        </div>
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>                                                            
                    </fieldset>      
                    <fieldset>
                        <legend>My Sizes:</legend>
                        <label>
                        @foreach($sizes as $size)
                        <div class="check_container">
                            <div class="cat gender">
                               <label>
                                  <input name="gender_id" type="radio" value="{{$size['gender_id']}}" id="gender_{{$size['gender_id']}}" onclick="showCategory({{$size['gender_id']}})" required><span>{{$size['gender_name']}}</span>
                               </label>
                            </div>
                        </div>
                        {{-- 
                        <label for="gender_{{$size['gender_id']}}"><input type="radio" name="gender_id" id="gender_{{$size['gender_id']}}" value="{{$size['gender_id']}}" onclick="showCategory({{$size['gender_id']}})"> {{$size['gender_name']}}</label>&nbsp;&nbsp;&nbsp; --}}
                        @endforeach
                        </label>
                        @error('gender_id')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <br>                                          
                        @foreach($sizes as $size)
                            <div id="cat_{{$size['gender_id']}}" style="display: none;">
                            @foreach($size['categories'] as $category)
                            @if(count($category['sizes']) > 0)
                            <div class="form-group">
                                
                                <label for="{{$category['name']}}"><img src="{{$category['image']}}">{{$category['name']}}</label>
                                @php
                                    $newsize = collect($category['sizes']);
                                @endphp
                                @foreach($newsize->chunk(12) as $_sizes)
                                <div class="row">
                                    @foreach($_sizes as $new_size)
                                        <div class="col-md-1">
                                                
                                            <div class="check_container">
                                                <div class="cat sizes">
                                                   <label>
                                                      <input name="size_{{$size['gender_id']}}[]" type="checkbox" value="{{$new_size['id']}}"><span>{{$new_size['name']}}</span>
                                                   </label>
                                                </div>
                                            </div>                                          
                                        </div>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>                        
                            @endif
                            @endforeach
                            </div>
                        @endforeach                        

                    </fieldset>                                                                   

                    <button type="submit" class="btn btn-gradient-primary mr-2">Add</button>
                    <button type="reset" class="btn btn-light">Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>
@section('script')
    <script type="text/javascript">
        $(document).ready(function(){
             localStorage.removeItem('city');

        });
        function showCategory(gender){
            if(gender == 1){
                $("#cat_1").show();
                $("#cat_2").hide();
            }else if(gender == 2){
                $("#cat_1").hide();
                $("#cat_2").show();
            }else{
                $("#cat_1").hide();
                $("#cat_2").hide();                
            }
        }
        window.onload = (event) => {
          localStorage.removeItem('city');
        };
    </script>

 <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initialize" async defer></script>
    <script src="{{ asset('admin/assets/js/mapInput.js')}}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\AddAdminUserRequest','#userCreate') !!}
@endsection
@endsection