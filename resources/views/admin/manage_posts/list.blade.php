@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body"> 
			
                <!--<form class="form-inline float-right" action="{{route('appPosts')}}" method="GET">-->
				<button type="button" class="btn-sm btn-gradient-primary mb-2 mr-2 float-right" data-toggle="modal" data-target="#exampleModal">
				  Filter
				</button>
				<!-- <label style="margin-right:10px;" >Filter:</label>
					<select name="location" class="form-control mb-2 mr-sm-2" style="margin-right:10px;">
				   <option value=" " >Select Locations</option>
					@foreach($locations as $l)
					<option value="{{$l->city}}">{{$l->city}}</option>
					@endforeach
				   </select>				
				
                    <input type="text" value="{{isset($filter['search'])?$filter['search']:''}}" name="search" class="form-control mb-2 mr-sm-2" placeholder="Search" style="margin-right:10px;"/>
                    <button type="submit" title="Filter" name="filter" class="btn-sm btn-gradient-primary mb-2 mr-2"><i class="mdi mdi-filter"></i></button>
                    <button type="reset" onclick="location.href = '{{route('appPosts')}}'" title="Reset Filter" class="btn-sm btn-gradient-danger mb-2 mr-2"><i class="mdi mdi-refresh"></i></button>
					
                </form> -->
				
				<div class="filter-buttons float-left">
				<div class="list-view-button filtr_btn active"><i class="mdi mdi-view-list" aria-hidden="true"></i></div>
				  <div class="grid-view-button filtr_btn"><i class="mdi mdi-grid"></i></div>
				</div>
                <div class="clearfix"></div>
                @if(count($users) > 0)
                <div class="table-horizontal-scroll list_view">		
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th> Username </th>
                                <th> Brand Name </th>
                                <th> Title </th>
                                <th> Description </th>
                                <th> Other Information </th>
                                <th> Date & Time </th>
                                <th> Action </th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($users as $user)
                            @php
                            if($user->is_sold == '1'){
                            $sold  = 'sold';
                            }else{
                            $sold = 'Available';
                            }
							if(count($user->getLocations($user->locations))>=1){
								$loc = $user->getLocations($user->locations);
								$location = $loc[0]->city;
								}else{
								$location ='Location Not available';
							}
								$style = $user->getStyle($user->style_id);
							
                            $Otherdetails = '';
                            $Otherdetails .= '<button type="button" data-toggle="modal" data-target="#omyModal'.$user->id.'" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button>
                        <div class="modal fade" id="omyModal'.$user->id.'" role="dialog">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content" style="background-color:#ffff">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Other Informations</h4>
                                    </div>
                                    <div class="modal-body">';
                                        $Otherdetails .= '<strong>Gender:</strong> '.$user->gender->name.'<br/><br/>
                                        <strong>Category:</strong> '.$user->getpostcategoriesname().'<br/><br/>
                                        <strong>Size:</strong> '.$user->getpostsizename().'<br/><br/>
                                        <strong>Condition:</strong> '.$user->condition->name.'<br/><br/>
                                        <strong>Colors:</strong> '.$user->getpostcolorsname().'<br/><br/>
                                        <strong>Price Type:</strong> ';
                                        if($user->priceType){
                                        $Otherdetails .= $user->priceType->name;
                                        }
                                        $Otherdetails .= '<br/><br/>
                                        <strong>Price:</strong> '.$user->price.'<br/><br/>
                                        <strong>Sold:</strong> '.$sold.'
                                        <br/><br/><strong>Location:</strong> '.$location.'
                                        <br/><br/><strong>Style:</strong> '.$style.'
                                        <br/><br/>';
                                        $Otherdetails .= '
                                    </div><div class="modal-footer">
                                        <button type="button" class="btn-sm btn-gradient-danger btn-fw" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>';


                        @endphp
                        <tr>

                            <td> {{$user->user->username}} </td>
                            <td> {{($user->brand)?$user->brand->name:''}} </td>
                            <td> {{$user->title}} </td>
                            <td> {{$user->description}} </td>
                            <td> {!! $Otherdetails !!} </td>
                            <td> {{$user->created_at}} </td>
                            <td>
                                <a href="{{route('appPostsDelete',$user->id)}}" class="delete"><button type="button" title="Delete" class="btn-sm btn-gradient-danger btn-fw"><i class="mdi mdi-delete"></i></button></a>

                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
				<div class="thumbnail_view">
				<div class="row">
				<?php foreach($users as $user){
					$images  = $user->getImages($user->images);
					
					$Otherdetails = '';
					 if($user->is_sold == '1'){
                            $sold  = 'sold';
                            }else{
                            $sold = 'Available';
                            }
					if(count($user->getLocations($user->locations))>=1){
								$loc = $user->getLocations($user->locations);
								$location = $loc[0]->city;
								}else{
								$location ='Location Not available';
							}
							$style = $user->getStyle($user->style_id);
                            $Otherdetails .= '<button type="button" data-toggle="modal" data-target="#omyModal123'.$user->id.'" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button>
                        <div class="modal fade" id="omyModal123'.$user->id.'" role="dialog">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content" style="background-color:#ffff">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Other Informations</h4>
                                    </div>
                                    <div class="modal-body">';
                                        $Otherdetails .= '<strong>Gender:</strong> '.$user->gender->name.'<br/><br/>
                                        <strong>Category:</strong> '.$user->getpostcategoriesname().'<br/><br/>
                                        <strong>Size:</strong> '.$user->getpostsizename().'<br/><br/>
                                        <strong>Condition:</strong> '.$user->condition->name.'<br/><br/>
                                        <strong>Colors:</strong> '.$user->getpostcolorsname().'<br/><br/>
                                        <strong>Price Type:</strong> ';
                                        if($user->priceType){
                                        $Otherdetails .= $user->priceType->name;
                                        }
                                        $Otherdetails .= '<br/><br/>
                                        <strong>Price:</strong> '.$user->price.'<br/><br/>
                                        <strong>Sold:</strong> '.$sold.'
                                        <br/><br/><strong>Location:</strong> '.$location.'
                                        <br/><br/><strong>Style:</strong> '.$style.'
                                        <br/><br/><br/>';
                                        $Otherdetails .= '
                                    </div><div class="modal-footer">
                                        <button type="button" class="btn-sm btn-gradient-danger btn-fw" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
					
					
					?>
				<div class="col-md-3">
					<div class="post_thumb">
					<img src="<?= isset($images[0]['image']) ? $images[0]['image'] : ''; ?>" width="245px" height="200px">
					<div class="description">
						<div class="title"> 
						<h5><?php echo substr($user->title, 0, 25);?></h5>
						  <p> {{$user->user->username}} </p>
                            <p> {{($user->brand)?$user->brand->name:''}} </p>
							<p>{{$user->created_at}}</p>
						<?php echo $Otherdetails;?>
                        <a href="{{route('appPostsDelete',$user->id)}}" class="delete"><button type="button" title="Delete" class="btn-sm btn-gradient-danger btn-fw"><i class="mdi mdi-delete"></i></button></a>
						</div>
					</div>
					</div>
				</div>
				<?php }?>
				</div>
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
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Filter</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{route('appPosts')}}" method="GET" >
		  <div class="form-group">
			<label for="exampleInputEmail1">Post title</label>
			<input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Post title" name="title">
		  </div>
		  <div class="form-group">
			<label for="exampleInputPassword1">Department</label>
			<select class="form-select select2 form-control" aria-label="Default select example" name="gender_id">
			  <option value="">Select Gender</option>
			  <option value="1">Menswear</option>
			  <option value="2">Womenwear</option>
			</select>
		  </div>
		  <div class="form-group">
			<label for="exampleInputPassword1">Location</label>
			<select name="location" class="form-select form-control" style="margin-right:10px;">
				   <option value=" " >Select Locations</option>
					@foreach($locations as $l)
					@if($l->city !== '')
					<option value="{{$l->city}}">{{$l->city}}</option>
					@endif
					@endforeach
				   </select>	
		  </div>
		  <div class="form-group">
			<label for="exampleInputPassword1">Categories</label>
			<select name="category" class="form-select form-control" style="margin-right:10px;">
			<option value=" " >Select Categories</option>
					@foreach($categories as $l)
					@if($l->name !== '')
					<option value="{{$l->id}}">@if($l->parent_id !== 0){{'--'}} @endif {{$l->name}}</option>
					@endif
					@endforeach
				  
			</select>	
		  </div>
		  <div class="form-group">
			<label for="exampleInputPassword1">Style</label>
			<select name="style" class="form-select form-control" style="margin-right:10px;">
				   <option value=" " >Select style</option>
				   <?php //echo "<pre>"; print_r($styles);echo "</pre>"; exit;?>
					@foreach($styles as $l)
					@if($l->name !== '')
					<option value="{{$l->id}}">{{$l->name}}</option>
					@endif
					@endforeach
				   </select>	
		  </div>
		  <div class="form-group">
			<label for="exampleInputPassword1">Brand</label>
			<select name="brand" class="form-select form-control" style="margin-right:10px;">
				   <option value=" " >Select Brand</option>
					@foreach($brands as $l)
					@if($l->name !== '')
					<option value="{{$l->id}}">{{$l->name}}</option>
					@endif
					@endforeach
				   </select>	
		  </div>
		  
		  
		  <div class="form-group">
			<label for="exampleInputPassword1">Post status</label>
			<select name="is_sold" class="form-select form-control" style="margin-right:10px;">
				   <option value=" " >Select Status</option>
					<option value="1">Sold</option>
			</select>	
		  </div>
		  
		  <button type="submit" class="btn btn-primary" name="search">Submit</button>
		</form>
      </div>
     
    </div>
  </div>
</div>
@endsection
<style>
.filter-buttons {
    display: flex;
    padding: 10px;
}
.filtr_btn {
    padding: 10px;
	border: 1px solid #000;
	color: #000;
}
.filtr_btn.active i {
    color: #fff;
}
.filtr_btn.active {
    background: #5e50f9;
}
.post_thumb{
	border: 1px solid #eee;
	
}
.post_thumb button{
	font-size: 14px !important;
}
.post_thumb .title {
    padding: 10px;
}
</style>
@section('script')
<script>
    $(document).ready(function () {
        $('body').on('click', '.delete', function (e) {
            e.preventDefault();
            var msg = "Once deleted, you will not be able to recover this post";
            deleteWarning(msg, $(this).attr('href'));
        });
        $('body').on('click', '.switch_publish', function (e) {
            var url = '{{route("appPostsPublish")}}';
            var value = $(this).val();
            publishStatus(value, url);
        });
    });
	$(document).ready(function () {
		$('.thumbnail_view').hide();
		$('.grid-view-button').on('click',function(){
			$(this).addClass("active");
			$('.list-view-button').removeClass("active");
			$('.thumbnail_view').show();
			$('.list_view').hide();
		});
		$('.list-view-button').on('click',function(){
			$(this).addClass("active");
			$('.grid-view-button').removeClass("active");
			$('.thumbnail_view').hide();
			$('.list_view').show(); 
		});
	});
</script>
@endsection