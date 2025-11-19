@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body"> 
				 <form class="form-inline float-right" action="{{route('Notifications')}}" method="GET">
                    <input type="text" value="{{isset($filter['search'])?$filter['search']:''}}" name="search" class="form-control mb-2 mr-sm-2" placeholder="Search" />
                    <button type="submit" title="Filter" class="btn-sm btn-gradient-primary mb-2 mr-2"><i class="mdi mdi-filter"></i></button>
                    <button type="reset" onclick="location.href ='{{route('Notifications')}}'" title="Reset Filter" class="btn-sm btn-gradient-danger mb-2 mr-2"><i class="mdi mdi-refresh"></i></button>
                    @permission('notifications-create')
                    <button type="button" onclick="location.href ='{{route('NotificationsCreate')}}'" title="Add Notification" class="btn-sm btn-gradient-primary mb-2">Add Notification</button>
                    @endpermission
                </form>
				 <div class="clearfix"></div>
                @if(count($notifications) > 0)
               
               
                <div class="table-horizontal-scroll">		
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th> Heaer </th>
                                <th style="width=30%"> Sub Text </th>
                                <th> Photo </th>
                                <th> Send To </th>                                
                                <th> Show in Notification Log </th>
                                <th>Date/Time</th>
                                <th> Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $ignore = [1,2]; @endphp
                            @foreach($notifications as $notification)
                            {{-- @php
                            $usersdata = App\User::find($notification->custom_user_ids);
                            $udata = json_decode($usersdata);
                            if(isset($usersdata) && $usersdata != ''){
                            $username = $udata->name;
                            }else{
                            $username = 'All';
                            }
                            @endphp --}}
                        <div class="modal fade" id="omyModal{{$notification->id}}" role="dialog">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content" style="background-color:#ffff">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Notification Information</h4>
                                    </div>
                                    <div class="modal-body">
                                        <strong>Header:</strong> {{$notification->header}}<br/><br/>
                                        <strong>Sub text:</strong> {!! nl2br($notification->subtext)!!}<br/><br/>
                                    </div><div class="modal-footer">
                                        <button type="button" class="btn-sm btn-gradient-danger btn-fw" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <tr>
                                <td> {{$notification->header}} </td>
                                <td> <button type="button" data-toggle="modal" data-target="#omyModal{{$notification->id}}" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button> </td>
                                <td> <img src="{{$notification->image}}"> </td>
                                <td> {{$notification->send_notification_to}} </td>                                
                                <td> {{($notification->show_notificatoin_log ==1)?'Yes':'No'}} </td>                                                                
                                 <td> {{date('F d Y h:i A', strtotime($notification->created_at))}} </td>

                                <td>
                                    <a href="{{route('NotificationsDelete',$notification->id)}}" class="delete"><button type="button" title="Delete" class="btn-sm btn-gradient-danger btn-fw"><i class="mdi mdi-delete"></i></button>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <div class="float-right">
                        {{$notifications->appends($_REQUEST)->links()}}
                    </div>
                    <div class="float-left">
                        @include('admin.layouts.show_records_per_page')
                    </div>
                </div>
                @else
                <div class="alert alert-danger" role="alert">
                    No data found.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function(){
    $('body').on('click', '.delete', function(e){
    e.preventDefault();
    var msg = "Once deleted, you will not be able to recover this user";
    deleteWarning(msg, $(this).attr('href'));
    });
    $('body').on('click', '.switch_publish', function(e){
    var url = '{{route("NotificationsPublish")}}';
    var value = $(this).val();
    publishStatus(value, url);
    });
    });
</script>
@endsection