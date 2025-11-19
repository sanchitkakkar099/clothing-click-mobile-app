<!-- partial:partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
                <div class="nav-profile-image">
                    <img src="{{ResizeImage(Auth::guard('admin')->user()->image_id,44,44)}}" alt="profile">
                    <span class="login-status online"></span>
                    <!--change to offline or busy as needed-->
                </div>
                <div class="nav-profile-text d-flex flex-column">
                    <span class="font-weight-bold mb-2">{{Auth::guard('admin')->user()->name}}</span>
                    <span class="text-secondary text-small">{{Auth::guard('admin')->user()->adminRole->name}}</span>
                </div>
                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('appRegistation')}}">
                <span class="menu-title">Registration</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
          <li class="nav-item">
            <a class="nav-link" href="{{route('appPosts')}}">
                <span class="menu-title">Posts</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminBanner')}}">
                <span class="menu-title">Banners</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
<!--        <li class="nav-item">
            <a class="nav-link" href="{{route('adminLocationCity')}}">
                <span class="menu-title">Location (City)</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminGender')}}">
                <span class="menu-title">Genders</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>-->
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminCategory')}}">
                <span class="menu-title">Categories</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminSize')}}">
                <span class="menu-title">Sizes</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>		<li class="nav-item">
            <a class="nav-link" href="{{route('adminStyle')}}">
                <span class="menu-title">Style</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>		<li class="nav-item">
            <a class="nav-link" href="{{route('adminFaq')}}">
                <span class="menu-title">FAQs</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminColor')}}">
                <span class="menu-title">Colors</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminCondition')}}">
                <span class="menu-title">Conditions</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
         <li class="nav-item">
            <a class="nav-link" href="{{route('adminCoins')}}">
                <span class="menu-title">Coins</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminBrand')}}">
                <span class="menu-title">Brands</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
		<li class="nav-item">
            <a class="nav-link" href="{{route('adminSoldAnalytics')}}">
                <span class="menu-title">Sold Items</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
		<li class="nav-item">
            <a class="nav-link" href="{{route('adminSellersAnalytics')}}">
                <span class="menu-title">Top Sellers</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminCode')}}">
                <span class="menu-title">Access Code</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>   
		<li class="nav-item">
            <a class="nav-link" href="{{route('adminReportedUsers')}}">
                <span class="menu-title">Reported Users</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>          
        <li class="nav-item">
            <a class="nav-link" href="{{route('Notifications')}}">
                <span class="menu-title">Notification</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>                        
      <!--   @permission('dashboard-list')
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminHome')}}">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon"></i>
            </a>
        </li>
        @endpermission
        @permission('contact_leads-list')
        <li class="nav-item">
            <a class="nav-link" href="{{route('adminContactLeads')}}">
                <span class="menu-title">Contact Leads</span>
                <i class="mdi mdi-phone menu-icon"></i>
            </a>
        </li>
        @endpermission-->
<!--        		@if(ManagePermission('admin_users-list') || ManagePermission('admin_roles-list') || ManagePermission('access_rights-list'))
                        <li class="nav-item">
                          <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                                <span class="menu-title">Admin Users</span>
                                <i class="menu-arrow"></i>
                                <i class="mdi mdi-account-network menu-icon"></i>
                          </a>
                          <div class="collapse" id="ui-basic">
                                <ul class="nav flex-column sub-menu">
                                  @permission('admin_roles-list')
                                        <li class="nav-item"> <a class="nav-link" href="{{route('adminRoleManager')}}">Manage Roles</a></li>
                                  @endpermission
                                  @permission('admin_users-list')
                                        <li class="nav-item"> <a class="nav-link" href="{{route('adminUserManager')}}">Manage Users</a></li>
                                  @endpermission
                                  @permission('access_rights-list')
                                        <li class="nav-item"> <a class="nav-link" href="{{route('adminAccessRights')}}">Access Rights</a></li>
                                  @endpermission
                                </ul>
                          </div>
                        </li>
                        @endif-->
        		@if(ManagePermission('general_settings-list'))
                        <li class="nav-item">
                          <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                                <span class="menu-title">Settings & More</span>
                                <i class="menu-arrow"></i>
                                <i class="mdi mdi-settings menu-icon"></i>
                          </a>
                          <div class="collapse" id="ui-basic">
                                <ul class="nav flex-column sub-menu">
                                  @permission('general_settings-list')
                                        <li class="nav-item"> <a class="nav-link" href="{{route('adminGeneralSettings')}}">General Settings</a></li>
										<li class="nav-item"> <a class="nav-link" href="{{route('adminMobileAppSettings')}}">Mobile App Settings</a></li>
                                  @endpermission
                                </ul>
                          </div>
                        </li>
                        @endif

    </ul>
</nav>
<!-- partial -->