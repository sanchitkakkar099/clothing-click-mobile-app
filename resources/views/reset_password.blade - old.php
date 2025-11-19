<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" method="POST" action="{{route('userUpdatePassword')}}" id="passwordUpdate">
                    @csrf
                    <?php
                    $path = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $pathFragments = explode('/', $path);
                    $end = end($pathFragments);
                    ?>
                    <input type="hidden" id="userid" name="userid" value="<?php echo $end; ?>">
                    <div class="form-group">
                        <label for="old_password">Old Password</label>
                        <input type="password" class="form-control" name="old_password" value="" placeholder="Old Password" required />
                        @error('old_password')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" class="form-control" name="password" value="" placeholder="New Password" required />
                        @error('password')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm New Password" />
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