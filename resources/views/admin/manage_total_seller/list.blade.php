@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body"> 
			<div class="row">
	    <br/>
	   <div class="col ">
		<h2>Top Sellers</h2>
		</div>
		
             
		
	</div>
		
		 <hr/>
                
				<form class="form-inline float-right" action="{{route('adminSellersAnalytics')}}" method="GET">
                   <select name="location" class="form-control">
				   <option value="">Select</option>
					@foreach($locations as $l)
					<option value="{{$l->city}}">{{$l->city}}</option>
					@endforeach
				   </select>
                    <button type="submit" title="Filter" class="btn-sm btn-gradient-primary mb-2 mr-2"><i class="mdi mdi-filter"></i></button>
                    
                </form>
				
                <div class="clearfix"></div>
                
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">
<div class="container">
	
		 
		 @if(count($details) > 0)
                <div class="table-horizontal-scroll list_view">		
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th> Username </th>
                                <th> Location </th>
                                <th> Total sold </th>
                                <th> This year </th>
                                <th> This month </th>
                                <th> Today </th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($details as $user)
                           
                        <tr>

                            <td> {{$user['user_details']}} </td>
                            <td> {{$user['location']}} </td>
                            <td> {{$user['total_sold']}} </td>
                            <td> {{$user['this_year']}} </td>
                            <td> {{$user['this_month']}}</td>
                            <td> {{$user['today']}} </td>
                            
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
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