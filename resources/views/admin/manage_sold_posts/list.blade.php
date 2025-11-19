@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body"> 
			<div class="row">
	    <br/>
	   <div class="col text-center">
		<h2>Sold Post Analytics</h2>
		</div>
		
             
		
	</div>
		<div class="row text-center">
	        <div class="col">
	        <div class="counter">
      <h2 class="timer count-title count-number" data-to="<?php echo $total_posts;?>" data-speed="1500"></h2>
       <p class="count-text ">Total Products Sold</p>
    </div>
	        </div>
              <div class="col">
               <div class="counter">
      <h2 class="timer count-title count-number" data-to="<?php echo $this_year;?>" data-speed="1500"></h2>
      <p class="count-text ">This year</p>
    </div>
              </div>
              <div class="col">
                  <div class="counter">
      <h2 class="timer count-title count-number" data-to="<?php echo $this_month;?>" data-speed="1500"></h2>
      <p class="count-text ">This Month</p>
    </div></div>
              <div class="col">
              <div class="counter">
      <h2 class="timer count-title count-number" data-to="<?php echo $today;?>" data-speed="1500"></h2>
      <p class="count-text ">Today</p>
    </div>
              </div>
         </div>
		 <hr/>
                <form class="form-inline float-right" action="{{route('appPosts')}}" method="GET">
				<label style="margin-right:10px;" >Filter:</label>
					<select name="location" class="form-control mb-2 mr-sm-2" style="margin-right:10px;">
				   <option value=" " >Select Locations</option>
					@foreach($locations as $l)
					<option value="{{$l->city}}">{{$l->city}}</option>
					@endforeach
				   </select>				
				
                    <input type="text" value="{{isset($filter['search'])?$filter['search']:''}}" name="search" class="form-control mb-2 mr-sm-2" placeholder="Search" style="margin-right:10px;"/>
                    <button type="submit" title="Filter" name="filter" class="btn-sm btn-gradient-primary mb-2 mr-2"><i class="mdi mdi-filter"></i></button>
                    <button type="reset" onclick="location.href = '{{route('appPosts')}}'" title="Reset Filter" class="btn-sm btn-gradient-danger mb-2 mr-2"><i class="mdi mdi-refresh"></i></button>
                </form>
				
				<div class="filter-buttons float-left">
				  <div class="list-view-button filtr_btn active"><i class="mdi mdi-view-list" aria-hidden="true"></i></div>
				  <div class="grid-view-button filtr_btn"><i class="mdi mdi-grid"></i></div>
				</div>
                <div class="clearfix"></div>
                
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">
<div class="container">
	
		 
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
                            $sold = '--';
                            }
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
                                        <br/><br/><br/>';
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
                            $Otherdetails .= '<button type="button" data-toggle="modal" data-target="#omModal'.$user->id.'" title="Update" class="btn-sm btn-gradient-primary mb-2 mr-2" style="font-size:18px"><i class="mdi mdi-information"></i></button>
                        <div class="modal fade" id="omModal'.$user->id.'" role="dialog">
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
					<img src="<?php echo $images[0]['image'];?>" width="245px" height="200px">
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
</div>
@endsection

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
	(function ($) {
	$.fn.countTo = function (options) {
		options = options || {};
		
		return $(this).each(function () {
			// set options for current element
			var settings = $.extend({}, $.fn.countTo.defaults, {
				from:            $(this).data('from'),
				to:              $(this).data('to'),
				speed:           $(this).data('speed'),
				refreshInterval: $(this).data('refresh-interval'),
				decimals:        $(this).data('decimals')
			}, options);
			
			// how many times to update the value, and how much to increment the value on each update
			var loops = Math.ceil(settings.speed / settings.refreshInterval),
				increment = (settings.to - settings.from) / loops;
			
			// references & variables that will change with each update
			var self = this,
				$self = $(this),
				loopCount = 0,
				value = settings.from,
				data = $self.data('countTo') || {};
			
			$self.data('countTo', data);
			
			// if an existing interval can be found, clear it first
			if (data.interval) {
				clearInterval(data.interval);
			}
			data.interval = setInterval(updateTimer, settings.refreshInterval);
			
			// initialize the element with the starting value
			render(value);
			
			function updateTimer() {
				value += increment;
				loopCount++;
				
				render(value);
				
				if (typeof(settings.onUpdate) == 'function') {
					settings.onUpdate.call(self, value);
				}
				
				if (loopCount >= loops) {
					// remove the interval
					$self.removeData('countTo');
					clearInterval(data.interval);
					value = settings.to;
					
					if (typeof(settings.onComplete) == 'function') {
						settings.onComplete.call(self, value);
					}
				}
			}
			
			function render(value) {
				var formattedValue = settings.formatter.call(self, value, settings);
				$self.html(formattedValue);
			}
		});
	};
	
	$.fn.countTo.defaults = {
		from: 0,               // the number the element should start at
		to: 0,                 // the number the element should end at
		speed: 1000,           // how long it should take to count between the target numbers
		refreshInterval: 100,  // how often the element should be updated
		decimals: 0,           // the number of decimal places to show
		formatter: formatter,  // handler for formatting the value before rendering
		onUpdate: null,        // callback method for every time the element is updated
		onComplete: null       // callback method for when the element finishes updating
	};
	
	function formatter(value, settings) {
		return value.toFixed(settings.decimals);
	}
}(jQuery));

jQuery(function ($) {
  // custom formatting example
  $('.count-number').data('countToOptions', {
	formatter: function (value, options) {
	  return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ',');
	}
  });
  
  // start all the timers
  $('.timer').each(count);  
  
  function count(options) {
	var $this = $(this);
	options = $.extend({}, options || {}, $this.data('countToOptions') || {});
	$this.countTo(options);
  }
});
</script>
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