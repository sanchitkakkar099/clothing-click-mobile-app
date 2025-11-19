<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Clothes</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/vendor.bundle.base.css') }}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{ asset('admin/assets/images/favicon.png') }}" />
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
          <div class="row flex-grow">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left p-5">
                <h2>
                  Reset Password
                </h2>
                <form class="forms-sample" method="POST" action="{{route('userUpdatePassword')}}" id="passwordUpdate">
                    @csrf
                    <?php
                    $path = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $pathFragments = explode('/', $path);
                    $end = end($pathFragments);
                    ?>
                    <input type="hidden" id="userid" name="userid" value="<?php echo $end; ?>">
                    <!--div class="form-group">
                        <label for="old_password">Old Password</label>
                        <input type="password" class="form-control" name="old_password" value="" placeholder="Old Password" required />
                        @error('old_password')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div-->
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" class="form-control" name="password"    pattern="^(?=.*[0-9])(?=.*[A-Z]).{8,}$" 
       title="Must contain at least one uppercase letter, one number, and be at least 8 characters long" value="" placeholder="New Password" required >
                        @error('password')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" class="form-control"    pattern="^(?=.*[0-9])(?=.*[A-Z]).{8,}$" 
       title="Must contain at least one uppercase letter, one number, and be at least 8 characters long" name="password_confirmation" placeholder="Confirm New Password" required>
                        @error('password_confirmation')
                        <span class="invalid-feedback" role="alert" style="display:block;">  
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-gradient-primary mr-2">Update</button>
                    <button type="reset" class="btn btn-light">Cancel</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
	<script>
document.getElementById('passwordUpdate').addEventListener('submit', function(e) {
    const password = document.querySelector('input[name="password"]').value;
    const confirmPassword = document.querySelector('input[name="password_confirmation"]').value;

    if (password !== confirmPassword) {
        e.preventDefault(); // Stop form submission
        alert('Password and Confirm Password must match!');
    }
});
</script>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset('admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('admin/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('admin/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('admin/assets/js/misc.js') }}"></script>
    <!-- endinject -->
  </body>
</html>