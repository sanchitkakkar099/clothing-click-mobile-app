@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body"> 
                <form class="form-inline float-right" action="{{route('adminBanner')}}" method="GET">
                    <button type="button" onclick="location.href ='{{route('adminBannerCreate')}}'" title="Add Banner" class="btn-sm btn-gradient-primary mb-2">Add Banner</button>
                </form>
                <div class="clearfix"></div>
                @if(count($users) > 0)
                <div class="table-horizontal-scroll">		
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th> Photo </th>
                                <th> Date & Time </th>
                                <th> Publish & Unpublish </th>
                                <th> Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>

                                <td> <img src="{{ResizeImage($user->photo,32,32)}}" /> </td>
                                <td> {{$user->created_at}} </td>

                                <td>
                                    @include('admin.layouts.publish_switch',['value'=>$user->id,'checked'=>$user->is_active])
                                </td>
                                <td>
                                    <a href="{{route('adminBannerEdit',$user->id)}}"><button type="button" title="Update" class="btn-sm btn-gradient-success btn-fw"><i class="mdi mdi-border-color"></i></button></a>
                                    <a href="{{route('adminBannerDelete',$user->id)}}" class="delete"><button type="button" title="Delete" class="btn-sm btn-gradient-danger btn-fw"><i class="mdi mdi-delete"></i></button></a>

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
    var url = '{{route("adminBannerPublish")}}';
    var value = $(this).val();
    publishStatus(value, url);
    });
    });
</script>
@endsection