@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body"> 
                <form class="form-inline float-right" action="{{route('adminCategory')}}" method="GET">
                    <input type="text" value="{{isset($filter['search'])?$filter['search']:''}}" name="search" class="form-control mb-2 mr-sm-2" placeholder="Search" />
                    <button type="submit" title="Filter" class="btn-sm btn-gradient-primary mb-2 mr-2"><i class="mdi mdi-filter"></i></button>
                    <button type="reset" onclick="location.href = '{{route('adminCategory')}}'" title="Reset Filter" class="btn-sm btn-gradient-danger mb-2 mr-2"><i class="mdi mdi-refresh"></i></button>
                    <button type="button" onclick="location.href = '{{route('adminCategoryCreate')}}'" title="Add Category" class="btn-sm btn-gradient-primary mb-2">Add Category</button>
                </form>
                <div class="clearfix"></div>
                @if(count($users) > 0)
                <div class="table-horizontal-scroll">		
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th> Name </th>
                                <th> Photo </th>
                                <th> Gender </th>
                                <th> Sub Category </th>
                                <th> Date & Time </th>
                                <th> Publish & Unpublish </th>
                                <th> Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>

                                <td> {{$user->name}} </td>
                                @if(isset($user->photo) && $user->photo != '')
                                <td> <img src="{{ResizeImage($user->photo,32,32)}}" /> </td>
                                @else
                                <td>-</td>
                                @endif
                                @php
                                $genderdata = App\gender::getGender($user->gender_id);
                                $udata = json_decode($genderdata);
                                if(isset($genderdata) && $genderdata != ''){
                                $gendername = $udata->name;
                                }else{
                                $gendername = '---';
                                }
                                if(isset($user->gender_id) && $user->gender_id != ''){
                                @endphp
                                <td> {{$gendername}} </td>
                                @php
                                }else{
                                @endphp
                                <td> --- </td>
                                @php
                                }
                                @endphp
                                @php
                                $subcatdata = App\category::getParentCategory($user->parent_id);
                                $sdata = json_decode($subcatdata);
                                if(isset($subcatdata) && $subcatdata != ''){
                                $subcatname = $sdata->name;
                                }else{
                                $subcatname = '---';
                                }
                                if(isset($user->parent_id) && $user->parent_id != ''){
                                @endphp
                                <td> {{$subcatname}} </td>
                                @php
                                }else{
                                @endphp
                                <td> --- </td>
                                @php
                                }
                                @endphp
                                <td> {{$user->created_at}} </td>
                                <td>
                                    @include('admin.layouts.publish_switch',['value'=>$user->id,'checked'=>$user->is_active])
                                </td>
                                <td>
                                    <a href="{{route('adminCategoryEdit',$user->id)}}"><button type="button" title="Update" class="btn-sm btn-gradient-success btn-fw"><i class="mdi mdi-border-color"></i></button></a>
                                  <a href="{{route('adminCategoryDelete',$user->id)}}" class="delete"><button type="button" title="Delete" class="btn-sm btn-gradient-danger btn-fw"><i class="mdi mdi-delete"></i></button></a>

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
    $(document).ready(function(){
    $('body').on('click', '.delete', function(e){
    e.preventDefault();
    var msg = "Once deleted, you will not be able to recover this user";
    deleteWarning(msg, $(this).attr('href'));
    });
    $('body').on('click', '.switch_publish', function(e){
    var url = '{{route("adminCategoryPublish")}}';
    var value = $(this).val();
    publishStatus(value, url);
    });
    });
</script>
@endsection