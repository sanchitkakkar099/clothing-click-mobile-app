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
                    @if(request()->code)
                    @else
                   
                    @endif
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
                                <th> Other Information </th>
                                <th> Device Information </th>
                                <th> Location Information </th>
                                <th> Date & Time </th>
                                <th>Status<br>Block/Un-block</th>
                                <th> Action </th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($users as $user)
                            @php
                            if($user->role_id == 1){
                            $role = 'User';
                            }else if($user->role_id == 8){
                            $role = 'Store';
                            }else if($user->role_id == 3){
                            $role = 'Brand';
                            }

                            $Otherdetails = '';
                            $Otherdetails .= '<button type="button" data-toggle="modal" data-target="#omyModal'.$user->id.'" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button>
                        <div class="modal fade" id="omyModal'.$user->id.'" role="dialog">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content" style="background-color:#ffff">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Device Informations</h4>
                                    </div>
                                    <div class="modal-body">';
                                        $Otherdetails .= '<strong>Username:</strong> '.$user->username.'<br/><br/>
                                        <strong>Country Code:</strong> '.$user->country_code.'<br/><br/>
                                        <strong>Country Prefix:</strong> '.$user->country_prefix.'<br/><br/>
                                        <strong>Website:</strong> '.$user->website.'<br/><br/>
                                        <strong>BIO:</strong> '.$user->bio.'
                                        <br/><br/><br/>';
                                        $Otherdetails .= '
                                    </div><div class="modal-footer">
                                        <button type="button" class="btn-sm btn-gradient-danger btn-fw" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>';


                        $ddata = App\user_logindevice_info::getDeviceData($user->id);
                        $devicedetails = '';
                        $devicedetails .= '<button type="button" data-toggle="modal" data-target="#myModal'.$user->id.'" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button>
                        <div class="modal fade" id="myModal'.$user->id.'" role="dialog">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content" style="background-color:#ffff">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Device Informations</h4>
                                    </div>
                                    <div class="modal-body"><div class="table-responsive">';

                                            $devicedetails .= '<table class="table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">ID</th>
                                                        <th scope="col">Devices Type</th>
                                                        <th scope="col">Devices Name</th>
                                                        <th scope="col">OS</th>
                                                        <th scope="col">Devices ID</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                                                    if(count($ddata) > 0){
                                                    foreach($ddata as $data){
                                                    $devicedetails .= '<tr>
                                                        <th scope="row">'.$data->id.'</th>
                                                        <td>'.$data->devices_type.'</td>
                                                        <td>'.$data->devices_name.'</td>
                                                        <td>'.$data->os.'</td>
                                                        <td>'.$data->devices_id.'</td>
                                                    </tr>';
                                                    }
                                                    }else{
                                                    $devicedetails .= '<strong>No Record Founds.</strong>';
                                                }
                                                $devicedetails .= '</tbody>
                                            </table>';
                                            $devicedetails .= '</div>
                                    </div><div class="modal-footer">
                                        <button type="button" class="btn-sm btn-gradient-danger btn-fw" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>';



                        $ldata = App\location::getLocationData($user->id);
                        $locationdetails = '';
                        $locationdetails .= '<button type="button" data-toggle="modal" data-target="#mylocation'.$user->id.'" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button>
                        <div class="modal fade" id="mylocation'.$user->id.'" role="dialog">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content" style="background-color:#ffff">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Location Informations</h4>
                                    </div>
                                    <div class="modal-body"><div class="table-responsive">';

                                            $locationdetails .= '<table class="table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">ID</th>
                                                        <th scope="col">Address</th>
                                                        <th scope="col">Postal Code</th>
                                                        <th scope="col">Latitude</th>
                                                        <th scope="col">Longitude</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
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


                                                    $locationdetails .= '<tr>
                                                        <th scope="row">'.$lldata->id.'</th>
                                                        <td>'.$lldata->address.'</td>
                                                        <td>'.$postal_code.'</td>
                                                        <td>'.$latitude.'</td>
                                                        <td>'.$longitude.'</td>
                                                    </tr>';
                                                    }
                                                    }else{
                                                    $locationdetails .= '<strong>No Record Founds.</strong>';
                                                }
                                                $locationdetails .= '</tbody>
                                            </table>';

                                            $locationdetails .= '</div>
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
                            <td> {!! $Otherdetails !!} </td>
                            <td> {!! $devicedetails !!} </td>
                            <td> {!! $locationdetails !!} </td>
                            <td> {{$user->created_at}} </td>
                            <Td>                                
                            @include('admin.layouts.publish_switch',['value'=>$user->id,'checked'=>$user->is_active])                         
                            </Td>                            
                            <td>
                                <a href="{{route('appRegistationDelete',$user->id)}}" class="delete"><button type="button" title="Delete" class="btn-sm btn-gradient-danger btn-fw"><i class="mdi mdi-delete"></i></button></a>

                            </td>
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