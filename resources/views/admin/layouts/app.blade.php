<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="robots" content="noindex, nofollow">
	<meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{config('app.name')}}</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/mdi/css/materialdesignicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/css/vendor.bundle.base.css')}}">
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{asset('admin/assets/css/style.css')}}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{asset('admin/assets/images/favicon.png')}}" />
    @yield('css')
  </head>
  <body class="">
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
          <a class="navbar-brand brand-logo" href="{{route('adminHome')}}"><img src="{{asset('admin/assets/images/logo.png')}}" alt="logo" /></a>
          <a class="navbar-brand brand-logo-mini" href="{{route('adminHome')}}"><img src="{{asset('admin/assets/images/logo-mini.svg')}}" alt="logo" /></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-stretch">
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
          </button>
          <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
              <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
                <div class="nav-profile-img">
                  <img src="{{ResizeImage(Auth::guard('admin')->user()->image_id,32,32)}}" alt="image">
                  <span class="availability-status online"></span>
                </div>
                <div class="nav-profile-text">
                  <p class="mb-1 text-black">{{Auth::guard('admin')->user()->name}}</p>
                </div>
              </a>
              <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
				@permission('user_profile-list')
					<a class="dropdown-item" href="{{route('adminUserProfile')}}"><i class="mdi mdi-account mr-2 text-success"></i> Profile </a>
					<div class="dropdown-divider"></div>
				@endpermission
				@permission('change_password-list')
					<a class="dropdown-item" href="{{route('adminChangePassword')}}"><i class="mdi mdi-account-key mr-2 text-primary"></i> Change Password </a>
					<div class="dropdown-divider"></div>
				@endpermission
				<a class="dropdown-item" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="mdi mdi-power mr-2 text-primary"></i>Logout</a>
              </div>
            </li>
            <li class="nav-item d-none d-lg-block full-screen-link">
              <a class="nav-link">
                <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
              </a>
            </li>
            <li class="nav-item nav-logout d-none d-lg-block">
              <a class="nav-link" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="mdi mdi-power"></i>
              </a>
			  <form id="logout-form" action="{{ route('adminLogout') }}" method="POST" style="display: none;">
					@csrf
				</form>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
       @include('admin.layouts.sidebar')
        <div class="main-panel">
          <div class="content-wrapper">
			@if(isset($breadcum) && is_array($breadcum))
			<div class="page-header">
				<h3 class="page-title">
					<span class="page-title-icon bg-gradient-primary text-white mr-2">
					  <i class="{{$breadcum['icon']}}"></i>
					</span> {{$breadcum['breadcum'][0]}} 
				</h3>
				<nav aria-label="breadcrumb">
					<ul class="breadcrumb">
					  @foreach($breadcum['breadcum'] as $key => $value)
						  <li class="breadcrumb-item active" aria-current="page">
							<span></span>{{$value}}
						  </li>
					  @endforeach
					</ul>
				</nav>
            </div>
			@endif
			
			@if(session('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				{{session('success')}}
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			  </button>
			</div>
			@endif
			
			@if(session('error'))
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				{{session('error')}}
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			  </button>
			</div>
			@endif
			
            @yield('content')
			  <!-- partial:partials/_footer.html -->
			  <!-- <footer class="footer">
				<div class="d-sm-flex justify-content-center justify-content-sm-between">
				  <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © {{date('Y')}} <a href="{{route('adminHome')}}" target="_blank">{{config('app.name')}}</a>. All rights reserved.</span>
				</div>
			  </footer>-->
			  <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{asset('admin/assets/vendors/js/vendor.bundle.base.js')}}"></script>
    <!-- endinject -->
    <!-- inject:js -->
	{{-- <script src="{{asset('admin/assets/js/off-canvas.js')}}"></script>
    <script src="{{asset('admin/assets/js/hoverable-collapse.js')}}"></script>
    <script src="{{asset('admin/assets/js/misc.js')}}"></script> --}}
	<script src="{{asset('admin/assets/js/combined.js')}}"></script>
    <script src="{{asset('vendor/jsvalidation/js/jsvalidation.min.js')}}"></script>
    <!-- endinject -->
	<script src="{{asset('admin/assets/js/sweetalert.js')}}"></script>
	<script src="{{asset('admin/assets/js/common.js')}}"></script>
	<script src="{{asset('admin/assets/js/jscolor.js')}}"></script>
	@yield('script')
  </body>
</html>