@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body"> 
                <form class="form-inline float-right" action="{{route('appRegistation')}}" method="GET">
                    <input type="text" value="{{isset($filter['search'])?$filter['search']:''}}" name="search" class="form-control mb-2 mr-sm-2" placeholder="Search" />
                    <button type="submit" title="Filter" class="btn-sm btn-gradient-primary mb-2 mr-2"><i class="mdi mdi-filter"></i></button>
                    <button type="reset" onclick="location.href = '{{route('appRegistation')}}'" title="Reset Filter" class="btn-sm btn-gradient-danger mb-2 mr-2"><i class="mdi mdi-refresh"></i></button>
                </form>
                <div class="clearfix"></div>
                @if(count($users) > 0)
                <div class="table-horizontal-scroll">		
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th> User Name </th>
                                <th> Role </th>
                                <th> Email </th>
                                <th> Phone No </th>
                                <th> Device Information </th>
                                <th> Location Information </th>
                                <th> Date & Time </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            @php
                            if($user->role_id == 1){
                            $role = 'User';
                            }else if($user->role_id == 2){
                            $role = 'Store';
                            }else if($user->role_id == 3){
                            $role = 'Brand';
                            }
                            $ddata = App\user_logindevice_info::getDeviceData($user->id);
                            $devicedetails = '';
                            $devicedetails .= '<button type="button" data-toggle="modal" data-target="#myModal" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button>
                            <div class="modal fade" id="myModal" role="dialog">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content" style="background-color:#ffff">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Device Informations</h4>
                                    </div>
                                    <div class="modal-body">';
                                        if(count($ddata) > 0){
                                            foreach($ddata as $data){
                                                $devicedetails .= '<strong>Devices Type:</strong> '.$data->devices_type.'<br/><br/>
                                                <strong>Login Type:</strong> '.$data->login_type.'<br/><br/>
                                                <strong>Devices Token:</strong> '.$data->devices_token.'<br/><br/>
                                                <strong>Devices Name:</strong> '.$data->devices_name.'<br/><br/>
                                                <strong>OS:</strong> '.$data->os.'<br/><br/>
                                                <strong>Devices ID:</strong> '.$data->devices_id.'
                                                <br/><br/><br/>';
                                            }
                                        }else{
                                         $devicedetails .= '<strong>No Record Founds.</strong>';
                                        }
                                    $devicedetails .= '
                                    </div><div class="modal-footer">
                                        <button type="button" class="btn-sm btn-gradient-danger btn-fw" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                        
                        
                        
                        $ldata = App\location::getLocationData($user->id);
                            $locationdetails = '';
                            $locationdetails .= '<button type="button" data-toggle="modal" data-target="#mylocation" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button>
                            <div class="modal fade" id="mylocation" role="dialog">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content" style="background-color:#ffff">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Location Informations</h4>
                                    </div>
                                    <div class="modal-body">';
                                        if(count($ldata) > 0){
                                            foreach($ldata as $lldata){
                                            if(isset($lldata->postal_code) && $lldata->postal_code != ''){
                                            $postal_code = $lldata->postal_code;
                                            }else{
                                            $postal_code = 'N/A';
                                            }
                                            if(isset($lldata->latitude) && $lldata->latitude != ''){
                                            $latitude = $lldata->latitude;
                                            }else{
                                            $latitude = 'N/A';
                                            }
                                            if(isset($lldata->longitude) && $lldata->longitude != ''){
                                            $longitude = $lldata->longitude;
                                            }else{
                                            $longitude = 'N/A';
                                            }
                                            if(isset($lldata->price) && $lldata->price != ''){
                                            $price = $lldata->price;
                                            }else{
                                            $price = 'N/A';
                                            }
                                                $locationdetails .= '<strong>Address:</strong> '.$lldata->address.'<br/><br/>
                                                <strong>Postal Code:</strong> '.$postal_code.'<br/><br/>
                                                <strong>Latitude:</strong> '.$latitude.'<br/><br/>
                                                <strong>Longitude:</strong> '.$longitude.'<br/><br/>
                                                <strong>Price:</strong> '.$price.'
                                                <br/><br/><br/>';
                                            }
                                        }else{
                                         $locationdetails .= '<strong>No Record Founds.</strong>';
                                        }
                                    $locationdetails .= '
                                    </div><div class="modal-footer">
                                        <button type="button" class="btn-sm btn-gradient-danger btn-fw" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                        @endphp
                        <tr>

                            <td> {{$user->username}} </td>
                            <td> {{$role}} </td>
                            <td> {{$user->email}} </td>
                            <td> {{$user->phone}} </td>
                            <td> {!! $devicedetails !!} </td>
                            <td> {!! $locationdetails !!} </td>
                            <td> {{$user->created_at}} </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <div class="float-right">
                        {{$users->appends($_REQUEST)->links()}}
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
    $(document).ready(function () {
        $('body').on('click', '.delete', function (e) {
            e.preventDefault();
            var msg = "Once deleted, you will not be able to recover this user";
            deleteWarning(msg, $(this).attr('href'));
        });
        $('body').on('click', '.switch_publish', function (e) {
            var url = '{{route("appRegistationPublish")}}';
            var value = $(this).val();
            publishStatus(value, url);
        });
    });
</script>
@endsection