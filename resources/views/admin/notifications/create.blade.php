@extends('admin.layouts.app')
@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style type="text/css">
.select2.select2-container.select2-container--default.select2-container--above.select2-container--focus {
        width: 100% !important;
}
.select2.select2-container.select2-container--default.select2-container--above {
    width: 100% !important;
}
#custom_user .select2 {
  width: 100% !important;
}
</style>
@endsection
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form id="notificationCreate" class="forms-sample" method="POST" action="{{route('NotificationsStore')}}" enctype="multipart/form-data">
                    @csrf


                    <div class="form-group">
                        <div class="form-check">
                          <label class="">
                            Show Notification in Log </label>
                            <br>
                            <label>
                            <input type="radio"  name="show_notificatoin_log" value="1"> Yes <i class="input-helper"></i></label><br>
                            <label>
                            <input type="radio"  name="show_notificatoin_log" value="0"> No <i class="input-helper"></i></label>
                        </div>
                        @error('show_notificatoin_log')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror                        
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                          <label class="">
                            Send Notification to </label>
                            <br>
                            <label>
                            <input type="radio"  name="send_notification_to"  value="men"> Men <i class="input-helper"></i></label><br>
                            <label>
                            <input type="radio"  name="send_notification_to"  value="women"> Women <i class="input-helper"></i></label><br>
                            <label><input type="radio"  name="send_notification_to"  value="all"> All <i class="input-helper"></i></label><br>      
                            <label>           
                            <input type="radio"  name="send_notification_to"  value="custom"> Custom <i class="input-helper"></i></label>                                                        
                        </div>
                        @error('send_notification_to')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group " id="custom_user" style="width:100%; display: none;">
                        <label for="name">Select User</label><br>
                        <select class="form-control selectusers" name="custom_user_ids[]" multiple placeholder="select">
                            @foreach($usersdata as $user)
                            <option value="{{$user->id}}">{{$user->username}}</option>
                            @endforeach
                        </select>
                    </div>                                                            
                    <div class="form-group">
                        <label for="name">Header <span class="req">*</span></label>
                        <input type="text" class="form-control" name="header" value="{{old('header')}}" placeholder="Header" required />
                        @error('header')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Sub Text</label>
                        <textarea class="form-control" name="subtext" placeholder="Subtext" rows="5">{{old('subtext')}}</textarea>
                        @error('subtext')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                     <div class="form-group">
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" name="photo" placeholder="Photo" />
                         @error('photo')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>                    



                    <button type="submit" class="btn btn-gradient-primary mr-2">Send</button>
                    <button type="reset" class="btn btn-light">Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>
@section('script')
{!! JsValidator::formRequest('App\Http\Requests\Admin\AddNotificationsRequest','#notificationCreate') !!}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#notificationCreate').change(function(){
            selected_value = $("input[name='send_notification_to']:checked").val();
            if(selected_value == 'custom'){
                $("#custom_user").show();
                $("#custom_user .select2").css('width','100% !important');
            }else{
                $("#custom_user").hide();
            }
        });
    
        $('.selectusers').select2();
    });

</script>
@endsection
@endsection