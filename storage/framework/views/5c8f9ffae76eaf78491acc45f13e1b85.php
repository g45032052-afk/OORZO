<!DOCTYPE html>
    <?php
    $log_email_succ = session()->get('log_email_succ');
    ?>
<html dir="<?php echo e($site_direction); ?>" lang="<?php echo e($locale); ?>" class="<?php echo e($site_direction === 'rtl'?'active':''); ?>">
<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php
        $app_name = \App\CentralLogics\Helpers::get_business_settings('business_name', false);
        $icon = \App\CentralLogics\Helpers::get_business_settings('icon', false);
    ?>
    <!-- Title -->
    <title><?php echo e(translate('messages.login')); ?> | <?php echo e($app_name??translate('STACKFOOD')); ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo e(asset($icon ? 'storage/app/public/business/'.$icon : 'public/favicon.ico')); ?>">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/vendor.min.css">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/vendor/icon-set/style.css">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/toastr.css">
</head>

<body>
<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="main auth-bg">
    <!-- Content -->
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <div class="auth-content">
            <div class="content">
                <h2 class="title text-uppercase"><?php echo e(translate('messages.welcome_to')); ?> <?php echo e($app_name??'STACKFOOD'); ?></h2>
                <p>
                    <?php echo e(translate('Manage_your_app_&_website_easily')); ?>

                </p>
            </div>
        </div>
        <div class="auth-wrapper">
            <div class="auth-wrapper-body auth-form-appear">
                <?php ($systemlogo=\App\Models\BusinessSetting::where(['key'=>'logo'])->first()); ?>
                <?php ($role = $role ?? null ); ?>
                <a class="auth-logo mb-5" href="javascript:">
                    <img class="z-index-2 onerror-image"
                    src="<?php echo e(\App\CentralLogics\Helpers::get_full_url('business',$systemlogo?->value,$systemlogo?->storage[0]?->value ?? 'public', 'authfav')); ?>"
                    data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/auth-fav.png')); ?>" alt="image">
                </a>
                <div class="text-center">
                    <div class="auth-header mb-5">
                        <?php if($role == 'vendor'): ?>
                        <h2 class="signin-txt"><?php echo e(translate('messages.Signin_To_Your_Restaurant_Panel')); ?></h2>
                        <?php else: ?>

                        <h2 class="signin-txt"><?php echo e(translate('messages.Signin_To_Your_Panel')); ?></h2>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="multi-language-change position-absolute start-0 top-0 mt-2">
                    


                </div>
                <!-- Content -->
                <label class="badge badge-soft-success float-right initial-1">
                    <?php echo e(translate('messages.software_version')); ?> : <?php echo e(env('SOFTWARE_VERSION')); ?>

                </label>
                <!-- Form -->
                <form class="login_form" action="<?php echo e(route('login_post')); ?>" method="post" id="form-id">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="role" value="<?php echo e($role ?? null); ?>">

                    <div class="__bg-F8F9FC-card mb-20">
                        <!-- Form Group -->
                        <div class="js-form-message form-group mb-3">
                            <label class="form-label text-capitalize" for="signinSrEmail"><?php echo e(translate('messages.your_email')); ?></label>
                            <input type="email" class="form-control form-control-lg" value="<?php echo e($email ?? ''); ?>" name="email" id="signinSrEmail"
                                tabindex="1" aria-label="email@address.com"
                                required data-msg="Please enter a valid email address.">
                            <div class="focus-effects"></div>
                        </div>
                        <!-- End Form Group -->
                        <!-- Form Group -->
                        <div class="js-form-message form-group">
                            <label class="form-label text-capitalize" for="signupSrPassword" tabindex="0">
                                <span class="d-flex justify-content-between align-items-center">
                                <?php echo e(translate('messages.password')); ?>

                                </span>
                            </label>
                            <div class="input-group input-group-merge">
                                <input type="password" class="js-toggle-password form-control form-control-lg __rounded"
                                    name="password" id="signupSrPassword" value="<?php echo e($password ?? ''); ?>"
                                    aria-label="<?php echo e(translate('messages.password_length_placeholder',['length'=>'6+'])); ?>" required
                                    data-msg="<?php echo e(translate('messages.invalid_password_warning')); ?>"
                                    data-hs-toggle-password-options='{
                                                "target": "#changePassTarget",
                                        "defaultClass": "tio-hidden-outlined",
                                        "showClass": "tio-visible-outlined",
                                        "classChangeTarget": "#changePassIcon"
                                        }'>

                                <div class="focus-effects"></div>
                                <div id="changePassTarget" class="input-group-append">
                                    <a class="input-group-text" href="javascript:">
                                        <i id="changePassIcon" class="tio-visible-outlined"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- End Form Group -->

                    </div>
                        <div class="form-group mb-3">

                            <?php ($recaptcha = \App\CentralLogics\Helpers::get_business_settings('recaptcha')); ?>
                            <?php if(isset($recaptcha) && $recaptcha['status'] == 1): ?>
                                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                                <input type="hidden" name="set_default_captcha" id="set_default_captcha_value" value="0" >
                                <div class="row p-2 d-none" id="reload-captcha">
                                    <div class="col-6 pr-0">
                                        <input type="text" class="form-control form-control-lg border-0" name="custome_recaptcha"
                                            id="custome_recaptcha" required placeholder="<?php echo e(translate('Enter recaptcha value')); ?>" autocomplete="off" value="<?php echo e(getEnvMode()=='dev'? session('six_captcha'):''); ?>">
                                    </div>
                                    <div class="col-6 bg-white rounded d-flex">
                                        <img src="<?php echo $custome_recaptcha->inline(); ?>" class="rounded w-100" />
                                        <div class="p-3 pr-0 capcha-spin reloadCaptcha">
                                            <i class="tio-cached"></i>
                                        </div>
                                    </div>
                                </div>

                            <?php else: ?>
                                <div class="row p-2" id="reload-captcha">
                                    <div class="col-6 pr-0">
                                        <input type="text" class="form-control form-control-lg border-0" name="custome_recaptcha"
                                            id="custome_recaptcha" required placeholder="<?php echo e(translate('Enter recaptcha value')); ?>" autocomplete="off" value="<?php echo e(getEnvMode()=='dev'? session('six_captcha'):''); ?>">
                                    </div>
                                    <div class="col-6 bg-white rounded d-flex">
                                        <img src="<?php echo $custome_recaptcha->inline(); ?>" class="rounded w-100" />
                                        <div class="p-3 pr-0 capcha-spin reloadCaptcha">
                                            <i class="tio-cached"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <!-- Checkbox -->
                    <div class="form-group mb-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <div class="custom-control custom-checkbox mb-0">
                                <input type="checkbox" class="custom-control-input" id="termsCheckbox" <?php echo e($password ? 'checked' : ''); ?>

                                    name="remember">
                                <label class="custom-control-label text-muted" for="termsCheckbox">
                                    <?php echo e(translate('messages.remember_me')); ?>

                                </label>
                            </div>
                            <!-- forget password -->
                            <div class="<?php echo e($role == 'admin' ? '' : 'd-none'); ?>" id="forget-password">
                                <div class="custom-control text-hover-primary">
                                    <span type="button" data-toggle="modal" data-target="#forgetPassModal"><?php echo e(translate('Forget_Password')); ?> ?</span>
                                </div>
                            </div>
                            <div class="<?php echo e($role == 'vendor' ? '' : 'd-none'); ?>" id="forget-password1">
                                <div class="custom-control text-hover-primary">
                                    <span type="button" data-toggle="modal" data-target="#forgetPassModal1"><?php echo e(translate('Forget_Password')); ?> ?</span>
                                </div>
                            </div>
                            <!-- End forget password -->
                        </div>
                    </div>
                    <!-- End Checkbox -->

                    <button type="submit" class="btn btn-lg btn-block btn-primary" id="signInBtn"><?php echo e(translate('messages.sign_in')); ?></button>
                     <?php if($role == 'admin'): ?>
                     <?php ($data = \App\Models\DataSetting::where('type', 'login_restaurant')->pluck('value')->first() ?? 'restaurant'); ?>
                     <p class="text-center mt-4 fs-14"><?php echo e(translate('Login as Restaurant Owner?')); ?> <a href="<?php echo e(url('/')); ?>/login/<?php echo e($data); ?>" class="text__primary font-semibold"><?php echo e(translate('Login Here')); ?></a></p>
                    <?php endif; ?>

                     <?php if($role == 'vendor'): ?>
                     <p class="text-center mt-4 fs-14"><?php echo e(translate('Don’t have account ?')); ?> <a href="<?php echo e(route('restaurant.create')); ?>" class="text__primary font-semibold"><?php echo e(translate('Registration Here')); ?></a></p>
                    <?php endif; ?>
                </form>
                <!-- End Form -->

                <!-- End Content -->
            </div>
            <?php if(getEnvMode() =='demo' ): ?>
                <?php if(isset($role) &&  $role == 'admin'): ?>
                    <div class="auto-fill-data-copy">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <span class="d-block"><strong>Email</strong> : admin@admin.com</span>
                                <span class="d-block"><strong>Password</strong> : 12345678</span>
                            </div>
                            <div>
                                <button class="btn btn-primary m-0" id="copy_cred"><i class="tio-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if(isset($role) &&  $role == 'vendor'): ?>
                    <div class="auto-fill-data-copy">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <span class="d-block"><strong>Email</strong> : test.restaurant@gmail.com</span>
                                <span class="d-block"><strong>Password</strong> : 12345678</span>
                            </div>
                            <div>
                                <button class="btn btn-primary m-0" id="copy_cred2"><i class="tio-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
<!-- ========== END MAIN CONTENT ========== -->


<div class="modal fade" id="forgetPassModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header justify-content-end">
          <span type="button" class="close-modal-icon" data-dismiss="modal">
              <i class="tio-clear"></i>
          </span>
        </div>
        <div class="modal-body">
          <div class="forget-pass-content">
              <img src="<?php echo e(dynamicAsset('assets/admin/img/send-mail.svg')); ?>" alt="">
              <!-- After Succeed -->
              <h4>
                  <?php echo e(translate('Send_Mail_to_Your_Email_?')); ?>

              </h4>
              <p>
                  <?php echo e(translate('A_mail_will_be_send_to_your_registered_email_with_a_link_to_change_passowrd')); ?>

              </p>
              <form action="<?php echo e(route('reset-password')); ?>" method="post">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="btn btn-lg btn-block btn--primary mt-3">
                      <?php echo e(translate('Send_Mail')); ?>

                  </button>
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="forgetPassModal1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header justify-content-end">
          <span type="button" class="close-modal-icon" data-dismiss="modal">
              <i class="tio-clear"></i>
          </span>
        </div>
        <div class="modal-body">
          <div class="forget-pass-content">
              <img src="<?php echo e(dynamicAsset('assets/admin/img/send-mail.svg')); ?>" alt="">
              <!-- After Succeed -->
              <h4>
                  <?php echo e(translate('messages.Send_Mail_to_Your_Email_?')); ?>

              </h4>
              <form class="" action="<?php echo e(route('vendor-reset-password')); ?>" method="post">
                  <?php echo csrf_field(); ?>

                  <input type="email" name="email" id="" class="form-control" required>
                  <button type="submit" class="btn btn-lg btn-block btn--primary mt-3"><?php echo e(translate('messages.Send_Mail')); ?></button>
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="successMailModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header justify-content-end">
            <span type="button" class="close-modal-icon" data-dismiss="modal">
                <i class="tio-clear"></i>
            </span>
          </div>
          <div class="modal-body">
            <div class="forget-pass-content">
                <!-- After Succeed -->
                <img src="<?php echo e(dynamicAsset('assets/admin/img/sent-mail.svg')); ?>" alt="">
                <h4>
                  <?php echo e(translate('Mail Sent to Registered Email Successfully')); ?>

                </h4>
                <p>
                  <?php echo e(translate('An email with password recovery instructions has been sent to your registered email address. Follow the link to reset your password.')); ?>

                </p>
            </div>
          </div>
        </div>
      </div>
    </div>


<!-- JS Implementing Plugins -->
<script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/vendor.min.js"></script>

<!-- JS Front -->
<script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/theme.min.js"></script>
<script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/toastr.js"></script>
<?php echo Toastr::message(); ?>


<?php if($errors->any()): ?>
    <script>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        toastr.error('<?php echo e(translate($error)); ?>');
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </script>
<?php endif; ?>
<?php if($log_email_succ): ?>
<?php (session()->forget('log_email_succ')); ?>
    <script>
        $('#successMailModal').modal('show');
    </script>
<?php endif; ?>

<script>
    // $("#forget-password").hide();
      $("#role-select").change(function() {
        var selectValue = $(this).val();
        if (selectValue == "admin") {
          $("#forget-password").show();
          $("#forget-password1").hide();
        } else if(selectValue == "vendor") {
          $("#forget-password").hide();
          $("#forget-password1").show();
        }
        else {
          $("#forget-password").hide();
          $("#forget-password1").hide();
        }
      });
</script>


<script>
    $(document).on('click','.reloadCaptcha', function(){
        $.ajax({
            url: "<?php echo e(route('reload-captcha')); ?>",
            type: "GET",
            dataType: 'json',
            beforeSend: function () {
                $('#loading').show()
                $('.capcha-spin').addClass('active')
            },
            success: function(data) {
                $('#reload-captcha').html(data.view);
            },
            complete: function () {
                $('#loading').hide()
                $('.capcha-spin').removeClass('active')
            }
        });
    });
</script>
<!-- JS Plugins Init. -->
<script>
    $(document).on('ready', function () {
        // INITIALIZATION OF SHOW PASSWORD
        // =======================================================
        $('.js-toggle-password').each(function () {
            new HSTogglePassword(this).init()
        });

        // INITIALIZATION OF FORM VALIDATION
        // =======================================================
        $('.js-validate').each(function () {
            $.HSCore.components.HSValidation.init($(this));
        });
    });
</script>

<?php if(isset($recaptcha) && $recaptcha['status'] == 1): ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo e($recaptcha['site_key']); ?>"></script>
<?php endif; ?>
<?php if(isset($recaptcha) && $recaptcha['status'] == 1): ?>
    <script>
        $(document).ready(function() {
            $('#signInBtn').click(function (e) {
                if( $('#set_default_captcha_value').val() == 1){
                    $('#form-id').submit();
                    return true;
                }
                e.preventDefault();
                if (typeof grecaptcha === 'undefined') {
                    toastr.error('Invalid recaptcha key provided. Please check the recaptcha configuration.');
                    $('#reload-captcha').removeClass('d-none');
                    $('#set_default_captcha_value').val('1');

                    return;
                }
                grecaptcha.ready(function () {
                    grecaptcha.execute('<?php echo e($recaptcha['site_key']); ?>', {action: 'submit'}).then(function (token) {
                        $('#g-recaptcha-response').value = token;
                        $('#form-id').submit();
                    });
                });
                window.onerror = function (message) {
                    var errorMessage = 'An unexpected error occurred. Please check the recaptcha configuration';
                    if (message.includes('Invalid site key')) {
                        errorMessage = 'Invalid site key provided. Please check the recaptcha configuration.';
                    } else if (message.includes('not loaded in api.js')) {
                        errorMessage = 'reCAPTCHA API could not be loaded. Please check the recaptcha API configuration.';
                    }
                    $('#reload-captcha').removeClass('d-none');
                    $('#set_default_captcha_value').val('1');
                    toastr.error(errorMessage)
                    return true;
                };
            });
        });
    </script>
<?php endif; ?>




<?php if(getEnvMode() =='demo'): ?>
    <script>
        $("#copy_cred").click(function() {
            $('#signinSrEmail').val('admin@admin.com');
            $('#signupSrPassword').val('12345678');
            toastr.success('Copied successfully!', 'Success!', {
                CloseButton: true,
                ProgressBar: true
            });
        })
        $("#copy_cred2").click(function() {
            $('#signinSrEmail').val('test.restaurant@gmail.com');
            $('#signupSrPassword').val('12345678');
            toastr.success('Copied successfully!', 'Success!', {
                CloseButton: true,
                ProgressBar: true
            });
        })
    </script>
<?php endif; ?>

</body>
</html>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/auth/login.blade.php ENDPATH**/ ?>