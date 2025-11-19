<div class="input-group-prepend">
  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{$pageRecords}}</button>
  <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 46px, 0px);">
	<a class="dropdown-item" href="{{route('adminSetRecords',10)}}" {{$pageRecords == 10 ? 'selected' : '' }} >10</a>
	<a class="dropdown-item" href="{{route('adminSetRecords',20)}}" {{$pageRecords == 20 ? 'selected' : '' }}>20</a>
	<a class="dropdown-item" href="{{route('adminSetRecords',50)}}" {{$pageRecords == 50 ? 'selected' : '' }}>50</a>
	<a class="dropdown-item" href="{{route('adminSetRecords',100)}}" {{$pageRecords == 100 ? 'selected' : '' }}>100</a>
  </div>
</div>