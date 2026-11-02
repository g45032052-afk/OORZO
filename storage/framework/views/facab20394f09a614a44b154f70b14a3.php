<?php $__env->startSection('title', translate('Settings')); ?>

<?php $__env->startSection('content'); ?>
    <div class="content">
        <form class="validate-form" action="<?php echo e(route('admin.business-settings.update-setup')); ?>" method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <div class="container-fluid">
                <div class="page-header pb-0">
                    <?php echo $__env->make('admin-views.business-settings.partials._note', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php echo $__env->make('admin-views.business-settings.partials.nav-menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="card mb-20" id="maintenance_mode">
                    <div class="card-body">
                        <div class="row g-3 justify-content-between">
                            <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                                <div class="">
                                    <h3 class="mb-1"><?php echo e(translate('messages.Maintenance_Mode')); ?></h3>
                                    <p class="fs-12 mb-0"><?php echo e(translate('messages.Turn on the Maintenance Mode will temporarily deactivate your selected systems as of your chosen date and time')); ?>.</p>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                                <div class="maintainance-mode-toggle-bar rounded d-flex justify-content-between border align-items-center w-100">
                                    <?php ($config = \App\CentralLogics\Helpers::get_business_settings('maintenance_mode')); ?>
                                    <h4 class="text-capitalize mb-0"><?php echo e(translate('messages.maintenance_mode')); ?></h4>

                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" id="maintenance_mode" class="status toggle-switch-input  <?php echo e(isset($config) && $config ?   'turn_off_maintenance_mode' : 'maintenance-mode'); ?> " <?php echo e(isset($config) && $config ? 'checked' : ''); ?>>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>

                                <?php
                                $maintenance_mode_data=   \App\Models\DataSetting::where('type','maintenance_mode')->whereIn('key' ,['maintenance_system_setup' ,'maintenance_duration_setup','maintenance_message_setup'])->pluck('value','key')
                                    ->map(function ($value) {
                                        return json_decode($value, true);
                                    })
                                    ->toArray();
                                $selectedMaintenanceSystem      =  data_get($maintenance_mode_data,'maintenance_system_setup',[]);
                                $selectedMaintenanceDuration    =  data_get($maintenance_mode_data,'maintenance_duration_setup',[]);
                                $selectedMaintenanceMessage     = data_get($maintenance_mode_data,'maintenance_message_setup',[]);
                                $maintenanceMode                = (int) ($config ?? 0);

                                if (isset($selectedMaintenanceDuration['start_date']) && isset($selectedMaintenanceDuration['end_date'])) {
                                    $startDate = new DateTime($selectedMaintenanceDuration['start_date']);
                                    $endDate = new DateTime($selectedMaintenanceDuration['end_date']);
                                } else {
                                    $startDate = null;
                                    $endDate = null;
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-20" id="basic_information">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-1"><?php echo e(translate('messages.Basic_Information')); ?></h3>
                            <p class="fs-12 mb-0"><?php echo e(translate('messages.here_you_setup_your_all_business_information')); ?>.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="card card-body">
                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                             <?php ($name = \App\CentralLogics\Helpers::get_business_settings('business_name', false)); ?>
                                            <div class="form-group mb-0">
                                                <label class="input-label d-flex align-items-center gap-1" for="restaurant_name">
                                                    <?php echo e(translate('messages.company_name')); ?>

                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="restaurant_name" maxlength="191" value="<?php echo e($name ?? ''); ?>" class="form-control"
                                                    placeholder="<?php echo e(translate('messages.Ex :')); ?> ABC Company" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                             <?php ($email = \App\Models\BusinessSetting::where('key', 'email_address')->first()); ?>
                                            <div class="form-group mb-0">
                                                <label class="input-label d-flex align-items-center gap-1" for="email">
                                                    <?php echo e(translate('messages.email')); ?>

                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="email" value="<?php echo e($email->value ?? ''); ?>" name="email"
                                                    class="form-control" placeholder="<?php echo e(translate('messages.Ex :')); ?> contact@company.com" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <?php ($phone = \App\Models\BusinessSetting::where('key', 'phone')->first()); ?>
                                            <div class="form-group mb-0">
                                                <label class="input-label d-flex align-items-center gap-1" for="phone">
                                                    <?php echo e(translate('messages.phone')); ?>

                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="tel" value="<?php echo e($phone->value ?? ''); ?>" name="phone"
                                                    class="form-control" placeholder="<?php echo e(translate('messages.Ex :')); ?> +9XXX-XXX-XXXX" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group mb-0">
                                                 <label class="input-label d-flex align-items-center gap-1"
                                                    for="country"><?php echo e(translate('messages.country')); ?>

                                                    <span class="text-danger">*</span>
                                                    <span class="tio-info text-gray1 fs-16"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="<?php echo e(translate('Choose_your_country_from_the_drop-down_menu.')); ?>">
                                                    </span>

                                                </label>
                                                <select id="country" name="country" class="form-control  js-select2-custom">
                                                    <option value="AF">Afghanistan</option>
                                                    <option value="AX">Åland Islands</option>
                                                    <option value="AL">Albania</option>
                                                    <option value="DZ">Algeria</option>
                                                    <option value="AS">American Samoa</option>
                                                    <option value="AD">Andorra</option>
                                                    <option value="AO">Angola</option>
                                                    <option value="AI">Anguilla</option>
                                                    <option value="AQ">Antarctica</option>
                                                    <option value="AG">Antigua and Barbuda</option>
                                                    <option value="AR">Argentina</option>
                                                    <option value="AM">Armenia</option>
                                                    <option value="AW">Aruba</option>
                                                    <option value="AU">Australia</option>
                                                    <option value="AT">Austria</option>
                                                    <option value="AZ">Azerbaijan</option>
                                                    <option value="BS">Bahamas</option>
                                                    <option value="BH">Bahrain</option>
                                                    <option value="BD">Bangladesh</option>
                                                    <option value="BB">Barbados</option>
                                                    <option value="BY">Belarus</option>
                                                    <option value="BE">Belgium</option>
                                                    <option value="BZ">Belize</option>
                                                    <option value="BJ">Benin</option>
                                                    <option value="BM">Bermuda</option>
                                                    <option value="BT">Bhutan</option>
                                                    <option value="BO">Bolivia, Plurinational State of</option>
                                                    <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                                                    <option value="BA">Bosnia and Herzegovina</option>
                                                    <option value="BW">Botswana</option>
                                                    <option value="BV">Bouvet Island</option>
                                                    <option value="BR">Brazil</option>
                                                    <option value="IO">British Indian Ocean Territory</option>
                                                    <option value="BN">Brunei Darussalam</option>
                                                    <option value="BG">Bulgaria</option>
                                                    <option value="BF">Burkina Faso</option>
                                                    <option value="BI">Burundi</option>
                                                    <option value="KH">Cambodia</option>
                                                    <option value="CM">Cameroon</option>
                                                    <option value="CA">Canada</option>
                                                    <option value="CV">Cape Verde</option>
                                                    <option value="KY">Cayman Islands</option>
                                                    <option value="CF">Central African Republic</option>
                                                    <option value="TD">Chad</option>
                                                    <option value="CL">Chile</option>
                                                    <option value="CN">China</option>
                                                    <option value="CX">Christmas Island</option>
                                                    <option value="CC">Cocos (Keeling) Islands</option>
                                                    <option value="CO">Colombia</option>
                                                    <option value="KM">Comoros</option>
                                                    <option value="CG">Congo</option>
                                                    <option value="CD">Congo, the Democratic Republic of the</option>
                                                    <option value="CK">Cook Islands</option>
                                                    <option value="CR">Costa Rica</option>
                                                    <option value="CI">Côte d'Ivoire</option>
                                                    <option value="HR">Croatia</option>
                                                    <option value="CU">Cuba</option>
                                                    <option value="CW">Curaçao</option>
                                                    <option value="CY">Cyprus</option>
                                                    <option value="CZ">Czech Republic</option>
                                                    <option value="DK">Denmark</option>
                                                    <option value="DJ">Djibouti</option>
                                                    <option value="DM">Dominica</option>
                                                    <option value="DO">Dominican Republic</option>
                                                    <option value="EC">Ecuador</option>
                                                    <option value="EG">Egypt</option>
                                                    <option value="SV">El Salvador</option>
                                                    <option value="GQ">Equatorial Guinea</option>
                                                    <option value="ER">Eritrea</option>
                                                    <option value="EE">Estonia</option>
                                                    <option value="ET">Ethiopia</option>
                                                    <option value="FK">Falkland Islands (Malvinas)</option>
                                                    <option value="FO">Faroe Islands</option>
                                                    <option value="FJ">Fiji</option>
                                                    <option value="FI">Finland</option>
                                                    <option value="FR">France</option>
                                                    <option value="GF">French Guiana</option>
                                                    <option value="PF">French Polynesia</option>
                                                    <option value="TF">French Southern Territories</option>
                                                    <option value="GA">Gabon</option>
                                                    <option value="GM">Gambia</option>
                                                    <option value="GE">Georgia</option>
                                                    <option value="DE">Germany</option>
                                                    <option value="GH">Ghana</option>
                                                    <option value="GI">Gibraltar</option>
                                                    <option value="GR">Greece</option>
                                                    <option value="GL">Greenland</option>
                                                    <option value="GD">Grenada</option>
                                                    <option value="GP">Guadeloupe</option>
                                                    <option value="GU">Guam</option>
                                                    <option value="GT">Guatemala</option>
                                                    <option value="GG">Guernsey</option>
                                                    <option value="GN">Guinea</option>
                                                    <option value="GW">Guinea-Bissau</option>
                                                    <option value="GY">Guyana</option>
                                                    <option value="HT">Haiti</option>
                                                    <option value="HM">Heard Island and McDonald Islands</option>
                                                    <option value="VA">Holy See (Vatican City State)</option>
                                                    <option value="HN">Honduras</option>
                                                    <option value="HK">Hong Kong</option>
                                                    <option value="HU">Hungary</option>
                                                    <option value="IS">Iceland</option>
                                                    <option value="IN">India</option>
                                                    <option value="ID">Indonesia</option>
                                                    <option value="IR">Iran, Islamic Republic of</option>
                                                    <option value="IQ">Iraq</option>
                                                    <option value="IE">Ireland</option>
                                                    <option value="IM">Isle of Man</option>
                                                    <option value="IL">Israel</option>
                                                    <option value="IT">Italy</option>
                                                    <option value="JM">Jamaica</option>
                                                    <option value="JP">Japan</option>
                                                    <option value="JE">Jersey</option>
                                                    <option value="JO">Jordan</option>
                                                    <option value="KZ">Kazakhstan</option>
                                                    <option value="KE">Kenya</option>
                                                    <option value="KI">Kiribati</option>
                                                    <option value="KP">Korea, Democratic People's Republic of</option>
                                                    <option value="KR">Korea, Republic of</option>
                                                    <option value="KW">Kuwait</option>
                                                    <option value="KG">Kyrgyzstan</option>
                                                    <option value="LA">Lao People's Democratic Republic</option>
                                                    <option value="LV">Latvia</option>
                                                    <option value="LB">Lebanon</option>
                                                    <option value="LS">Lesotho</option>
                                                    <option value="LR">Liberia</option>
                                                    <option value="LY">Libya</option>
                                                    <option value="LI">Liechtenstein</option>
                                                    <option value="LT">Lithuania</option>
                                                    <option value="LU">Luxembourg</option>
                                                    <option value="MO">Macao</option>
                                                    <option value="MK">Macedonia, the former Yugoslav Republic of</option>
                                                    <option value="MG">Madagascar</option>
                                                    <option value="MW">Malawi</option>
                                                    <option value="MY">Malaysia</option>
                                                    <option value="MV">Maldives</option>
                                                    <option value="ML">Mali</option>
                                                    <option value="MT">Malta</option>
                                                    <option value="MH">Marshall Islands</option>
                                                    <option value="MQ">Martinique</option>
                                                    <option value="MR">Mauritania</option>
                                                    <option value="MU">Mauritius</option>
                                                    <option value="YT">Mayotte</option>
                                                    <option value="MX">Mexico</option>
                                                    <option value="FM">Micronesia, Federated States of</option>
                                                    <option value="MD">Moldova, Republic of</option>
                                                    <option value="MC">Monaco</option>
                                                    <option value="MN">Mongolia</option>
                                                    <option value="ME">Montenegro</option>
                                                    <option value="MS">Montserrat</option>
                                                    <option value="MA">Morocco</option>
                                                    <option value="MZ">Mozambique</option>
                                                    <option value="MM">Myanmar</option>
                                                    <option value="NA">Namibia</option>
                                                    <option value="NR">Nauru</option>
                                                    <option value="NP">Nepal</option>
                                                    <option value="NL">Netherlands</option>
                                                    <option value="NC">New Caledonia</option>
                                                    <option value="NZ">New Zealand</option>
                                                    <option value="NI">Nicaragua</option>
                                                    <option value="NE">Niger</option>
                                                    <option value="NG">Nigeria</option>
                                                    <option value="NU">Niue</option>
                                                    <option value="NF">Norfolk Island</option>
                                                    <option value="MP">Northern Mariana Islands</option>
                                                    <option value="NO">Norway</option>
                                                    <option value="OM">Oman</option>
                                                    <option value="PK">Pakistan</option>
                                                    <option value="PW">Palau</option>
                                                    <option value="PS">Palestinian Territory, Occupied</option>
                                                    <option value="PA">Panama</option>
                                                    <option value="PG">Papua New Guinea</option>
                                                    <option value="PY">Paraguay</option>
                                                    <option value="PE">Peru</option>
                                                    <option value="PH">Philippines</option>
                                                    <option value="PN">Pitcairn</option>
                                                    <option value="PL">Poland</option>
                                                    <option value="PT">Portugal</option>
                                                    <option value="PR">Puerto Rico</option>
                                                    <option value="QA">Qatar</option>
                                                    <option value="RE">Réunion</option>
                                                    <option value="RO">Romania</option>
                                                    <option value="RU">Russian Federation</option>
                                                    <option value="RW">Rwanda</option>
                                                    <option value="BL">Saint Barthélemy</option>
                                                    <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
                                                    <option value="KN">Saint Kitts and Nevis</option>
                                                    <option value="LC">Saint Lucia</option>
                                                    <option value="MF">Saint Martin (French part)</option>
                                                    <option value="PM">Saint Pierre and Miquelon</option>
                                                    <option value="VC">Saint Vincent and the Grenadines</option>
                                                    <option value="WS">Samoa</option>
                                                    <option value="SM">San Marino</option>
                                                    <option value="ST">Sao Tome and Principe</option>
                                                    <option value="SA">Saudi Arabia</option>
                                                    <option value="SN">Senegal</option>
                                                    <option value="RS">Serbia</option>
                                                    <option value="SC">Seychelles</option>
                                                    <option value="SL">Sierra Leone</option>
                                                    <option value="SG">Singapore</option>
                                                    <option value="SX">Sint Maarten (Dutch part)</option>
                                                    <option value="SK">Slovakia</option>
                                                    <option value="SI">Slovenia</option>
                                                    <option value="SB">Solomon Islands</option>
                                                    <option value="SO">Somalia</option>
                                                    <option value="ZA">South Africa</option>
                                                    <option value="GS">South Georgia and the South Sandwich Islands</option>
                                                    <option value="SS">South Sudan</option>
                                                    <option value="ES">Spain</option>
                                                    <option value="LK">Sri Lanka</option>
                                                    <option value="SD">Sudan</option>
                                                    <option value="SR">Suriname</option>
                                                    <option value="SJ">Svalbard and Jan Mayen</option>
                                                    <option value="SZ">Swaziland</option>
                                                    <option value="SE">Sweden</option>
                                                    <option value="CH">Switzerland</option>
                                                    <option value="SY">Syrian Arab Republic</option>
                                                    <option value="TW">Taiwan, Province of China</option>
                                                    <option value="TJ">Tajikistan</option>
                                                    <option value="TZ">Tanzania, United Republic of</option>
                                                    <option value="TH">Thailand</option>
                                                    <option value="TL">Timor-Leste</option>
                                                    <option value="TG">Togo</option>
                                                    <option value="TK">Tokelau</option>
                                                    <option value="TO">Tonga</option>
                                                    <option value="TT">Trinidad and Tobago</option>
                                                    <option value="TN">Tunisia</option>
                                                    <option value="TR">Turkey</option>
                                                    <option value="TM">Turkmenistan</option>
                                                    <option value="TC">Turks and Caicos Islands</option>
                                                    <option value="TV">Tuvalu</option>
                                                    <option value="UG">Uganda</option>
                                                    <option value="UA">Ukraine</option>
                                                    <option value="AE">United Arab Emirates</option>
                                                    <option value="GB">United Kingdom</option>
                                                    <option value="US">United States</option>
                                                    <option value="UM">United States Minor Outlying Islands</option>
                                                    <option value="UY">Uruguay</option>
                                                    <option value="UZ">Uzbekistan</option>
                                                    <option value="VU">Vanuatu</option>
                                                    <option value="VE">Venezuela, Bolivarian Republic of</option>
                                                    <option value="VN">Viet Nam</option>
                                                    <option value="VG">Virgin Islands, British</option>
                                                    <option value="VI">Virgin Islands, U.S.</option>
                                                    <option value="WF">Wallis and Futuna</option>
                                                    <option value="EH">Western Sahara</option>
                                                    <option value="YE">Yemen</option>
                                                    <option value="ZM">Zambia</option>
                                                    <option value="ZW">Zimbabwe</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                             <?php ($address = \App\Models\BusinessSetting::where('key', 'address')->first()); ?>
                                            <div class="form-group mb-0">
                                                 <label class="input-label d-flex align-items-center gap-1"
                                                    for="address"><?php echo e(translate('messages.Description')); ?>

                                                    <span class="text-danger">*</span>
                                                </label>
                                                <textarea type="text" id="address" name="address" class="form-control" maxlength="100" placeholder="<?php echo e(translate('messages.Ex :')); ?> House#94, Road#8, Abc City" rows="1"
                                                    required><?php echo e($address->value ?? ''); ?></textarea>
                                                <div class="d-flex justify-content-end mt-1">
                                                    <span class="text-body-light fs-12">0/100</span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php ($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first()); ?>
                                        <?php ($default_location = $default_location->value ? json_decode($default_location->value, true) : 0); ?>
                                        <div class="col-sm-6">
                                            <div class="form-group mb-0">
                                                <label class="input-label text-capitalize d-flex alig-items-center"
                                                    for="latitude"><?php echo e(translate('messages.latitude')); ?>

                                                    <span class="tio-info text-gray1 fs-16"
                                                            data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('messages.Click_on_the_map_to_see_your_location’s_latitude')); ?>">
                                                    </span>
                                                </label>
                                                <input type="text" id="latitude" name="latitude" class="form-control d-inline"
                                                    placeholder="<?php echo e(translate('messages.Ex :')); ?> -94.22213"
                                                    value="<?php echo e($default_location ? $default_location['lat'] : 0); ?>" required readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group mb-0">
                                                <label class="input-label text-capitalize d-flex alig-items-center"
                                                    for="longitude"><?php echo e(translate('messages.longitude')); ?>

                                                    <span class="tio-info text-gray1 fs-16"
                                                            data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('messages.Click_on_the_map_to_see_your_location’s_longitude')); ?>">
                                                    </span>
                                                </label>
                                                <input type="text" name="longitude" class="form-control" placeholder="<?php echo e(translate('messages.Ex :')); ?> 103.344322"
                                                    id="longitude" value="<?php echo e($default_location ? $default_location['lng'] : 0); ?>"
                                                    required readonly>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <input id="pac-input" class="controls rounded border overflow-hidden initial-9 mt-1"
                                                    title="<?php echo e(translate('messages.search_your_location_here')); ?>" type="text"
                                                    placeholder="<?php echo e(translate('messages.search_here')); ?>" />
                                                <div id="location_map_canvas" class="overflow-hidden rounded h-170"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex flex-column h-100">
                                    <?php ($logo = \App\Models\BusinessSetting::where('key', 'logo')->first()); ?>
                                    <div class="p-xxl-20 p-12 global-bg-box rounded mb-20 h-100">
                                        <div class="pb-lg-1">
                                            <div class="mb-4">
                                                <label for="logo-input">
                                                    <h5 class="mb-1">
                                                        <?php echo e(translate('Logo')); ?>

                                                    </h5>
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <p class="mb-0 fs-12 gray-dark"><?php echo e(translate('Upload your Business Logo')); ?></p>
                                            </div>
                                            <div class="text-center">
                                                <?php echo $__env->make('admin-views.partials._image-uploader', [
                                                'id' => 'logo-input',
                                                'name' => 'logo',
                                                'ratio' => '3:1',
                                                'isRequired' => true ,
                                                'existingImage' => $logo?->value ? \App\CentralLogics\Helpers::get_full_url('business', $logo?->value ?? '', $logo?->storage[0]?->value ?? 'public','upload_image') : null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php ($icon = \App\Models\BusinessSetting::where('key', 'icon')->first()); ?>
                                    <div class="p-xxl-20 p-12 global-bg-box rounded h-100">
                                        <div class="pb-lg-1">
                                            <div class="mb-4">
                                                <label for="icon-input">
                                                    <h5 class="mb-1">
                                                        <?php echo e(translate('Favicon')); ?>

                                                    </h5>
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <p class="mb-0 fs-12 gray-dark"><?php echo e(translate('Upload your website favicon')); ?></p>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-center">
                                                <?php echo $__env->make('admin-views.partials._image-uploader', [
                                                'id' => 'icon-input',
                                                'name' => 'icon',
                                                'ratio' => '1:1',
                                                'isRequired' => true,
                                                'existingImage' => $icon?->value ? \App\CentralLogics\Helpers::get_full_url('business', $icon?->value ?? '', $icon?->storage[0]?->value ?? 'public','upload_image') : null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                    <div class="card-header" id="general_settings">
                        <div>
                            <h3 class="mb-1"><?php echo e(translate('messages.General_Settings')); ?></h3>
                            <p class="fs-12 mb-0"><?php echo e(translate('messages.Here you setup your all business general settings')); ?>.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card card-body p-12 p-xxl-20 mb-20">
                            <div class="mb-20">
                                <h4 class="mb-1"><?php echo e(translate('messages.Time_Setup')); ?></h4>
                                <p class="fs-12 mb-0"><?php echo e(translate('messages.Setup your business time zone and format from here')); ?>.</p>
                            </div>
                            <div class="bg-light rounded-10 p-12 p-xxl-20">
                                <div class="row g-3">
                                    <div class="col-lg-4 col-sm-6">
                                        <?php ($tz = \App\Models\BusinessSetting::where('key', 'timezone')->first()); ?>
                                        <?php ($settings_timezone = $tz ? $tz->value : 0); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1">
                                                <?php echo e(translate('messages.time_zone')); ?>

                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="timezone" class="form-control js-select2-custom">
                                                <?php $__currentLoopData = timezone_identifiers_list(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $dt = new DateTime("now", new DateTimeZone($tz));
                                                        $offset = $dt->getOffset(); // in seconds
                                                        $hours = intdiv($offset, 3600);
                                                        $minutes = abs(($offset % 3600) / 60);
                                                        $sign = $hours >= 0 ? '+' : '-';
                                                        $gmt = sprintf("GMT%s%02d:%02d", $sign, abs($hours), $minutes);
                                                    ?>
                                                    <option value="<?php echo e($tz); ?>" <?php echo e(isset($settings_timezone) && $settings_timezone == $tz ? 'selected' : ''); ?>>
                                                        (<?php echo e($gmt); ?>) <?php echo e($tz); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <option value="US/Central" <?php echo e(isset($settings_timezone) && $settings_timezone == 'US/Central' ? 'selected' :  ''); ?>> (GMT-06:00) Central Time (US & Canada)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        <?php ($tf = \App\Models\BusinessSetting::where('key', 'timeformat')->first()); ?>
                                        <?php ($tf = $tf ? $tf->value : '24'); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1">
                                                <?php echo e(translate('messages.time_format')); ?>

                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="resturant-type-group border bg-white">
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" value="12" name="time_format" <?php echo e($tf == '12' ? 'checked' : ''); ?>>
                                                    <span class="form-check-label">
                                                        <?php echo e(translate('messages.12_hour')); ?>

                                                    </span>
                                                </label>
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" value="24" name="time_format" <?php echo e($tf == '24' ? 'checked' : ''); ?>>
                                                    <span class="form-check-label">
                                                        <?php echo e(translate('messages.24_hour')); ?>

                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <?php ($distance_unit = \App\CentralLogics\Helpers::get_business_settings('distance_unit')); ?>
                        <?php ($distance_unit = $distance_unit ?: 'km'); ?>
                        <div class="card card-body p-12 p-xxl-20 mb-20" id="distance_unit_setup">
                            <div class="row g-3 align-items-center">
                                <div class="col-lg-8">
                                    <div>
                                        <h4 class="mb-1"><?php echo e(translate('messages.Distance_Unit')); ?></h4>
                                        <p class="fs-12 mb-0"><?php echo e(translate('messages.When measure any distance & need to show distance unit which unit will use.')); ?></p>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label d-flex align-items-center gap-1" for="distance_unit">
                                            <?php echo e(translate('messages.Distance_Unit')); ?>

                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="distance_unit" id="distance_unit" class="form-control js-select2-custom">
                                            <option value="km" <?php echo e($distance_unit == 'km' ? 'selected' : ''); ?>>
                                                <?php echo e(translate('messages.Kilometre')); ?> (km)
                                            </option>
                                            <option value="mi" <?php echo e($distance_unit == 'mi' ? 'selected' : ''); ?>>
                                                <?php echo e(translate('messages.Mile')); ?> (mi)
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php ($country_picker_status = \App\CentralLogics\Helpers::get_business_settings('country_picker_status')); ?>
                         <div class="card card-body mb-3">
                    <div class="row g-3 mb-20" id="country_picker">
                        <div class="col-lg-8">
                            <div>
                                <h4 class="mb-1"><?php echo e(translate('messages.Country Picker')); ?></h4>
                                <p class="fs-12 mb-0"><?php echo e(translate('messages.If you disable this option, no country picker will show on customer apps & websites.')); ?></p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <?php echo e(translate('messages.Status')); ?>

                                </span>
                                <input type="checkbox"
                                    data-id="country_picker_status"
                                    data-type="toggle"
                                       data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/mail-success.png')); ?>"
                                       data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/mail-warning.png')); ?>"
                                    data-title-on="<?php echo e(translate('Want to disable country picker')); ?> ?"
                                    data-title-off="<?php echo e(translate('Want to disable country picker')); ?> ?"
                                    data-text-on="<p><?php echo e(translate('If you enable this, user can select country from country picker')); ?></p>"
                                    data-text-off="<p><?php echo e(translate('If you disable this, user can not select country from country picker, default country will be selected')); ?></p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle"
                                    value="1"
                                    name="country_picker_status" id="country_picker_status"
                                    <?php echo e($country_picker_status == 1 ? 'checked' : ''); ?>

                                    >
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mb-2" style="--bs-bg-opacity: 0.1;">
                        <span class="text-warning lh-1 fs-14">
                            <i class="tio-info"></i>
                        </span>
                        <span>
                            <?php echo e(translate('messages.If you want to business multiple country you need to turn on country picker feature.')); ?>

                        </span>
                    </div>
                </div>


                        <div class="card card-body p-12 p-xxl-20 mb-20">
                            <div class="mb-20">
                                <h4 class="mb-1"><?php echo e(translate('messages.Currency_Setup')); ?></h4>
                                <p class="fs-12 mb-0"><?php echo e(translate('messages.Setup your business time zone and format from here')); ?>.</p>
                            </div>
                            <div class="bg-light rounded-10 p-12 p-xxl-20">
                                <div class="row g-3">
                                    <div class="col-lg-4 col-sm-6">
                                        <?php ($currency_code = \App\Models\BusinessSetting::where('key', 'currency')->first()); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1" for="exampleFormControlInput1">
                                                <?php echo e(translate('messages.currency')); ?> (<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>)
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select id="change_currency" name="currency" class="form-control js-select2-custom">
                                                <?php $__currentLoopData = \App\Models\Currency::orderBy('currency_code')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($currency['currency_code']); ?>"
                                                        <?php echo e($currency_code ? ($currency_code->value == $currency['currency_code'] ? 'selected' : '') : ''); ?>>
                                                        <?php echo e($currency['currency_code']); ?> ( <?php echo e($currency['currency_symbol']); ?> )
                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                         <?php ($currency_symbol_position = \App\Models\BusinessSetting::where('key', 'currency_symbol_position')->first()); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1" for="currency_symbol_position">
                                                <?php echo e(translate('messages.currency_symbol_positon')); ?>

                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="currency_symbol_position" class="form-control js-select2-custom"
                                                    id="currency_symbol_position">
                                                <option value="left"
                                                    <?php echo e($currency_symbol_position ? ($currency_symbol_position->value == 'left' ? 'selected' : '') : ''); ?>>
                                                    <?php echo e(translate('messages.left')); ?>

                                                    (<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>123)
                                                </option>
                                                <option value="right"
                                                    <?php echo e($currency_symbol_position ? ($currency_symbol_position->value == 'right' ? 'selected' : '') : ''); ?>>
                                                    <?php echo e(translate('messages.right')); ?>

                                                    (123<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>)
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6">
                                        <?php ($digit_after_decimal_point = \App\Models\BusinessSetting::where('key', 'digit_after_decimal_point')->first()); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1" for="digit_after_decimal_point">
                                                <?php echo e(translate('messages.Digit after decimal point')); ?>

                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" name="digit_after_decimal_point" class="form-control"
                                                id="digit_after_decimal_point"
                                                value="<?php echo e($digit_after_decimal_point ? $digit_after_decimal_point->value : 0); ?>"
                                                min="0" max="4" required>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card card-body p-12 p-xxl-20 mb-20" id="business_model">
                            <div class="mb-20">
                                <h4 class="mb-1"><?php echo e(translate('messages.Business_Model_Setup')); ?></h4>
                                <p class="fs-12 mb-0"><?php echo e(translate('messages.Setup your business model from here')); ?>.</p>
                            </div>
                            <div class="bg-light rounded-10 p-12 p-xxl-20">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="input-label d-flex align-items-center gap-1" for="exampleFormControlInput1">
                                            <?php echo e(translate('messages.Business_Model')); ?>

                                            <span class="text-danger">*</span>
                                            <span class="tio-info text-gray1 fs-16"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="<?php echo e(translate('messages.Choose the model that decides how you earn money and process orders.')); ?>">
                                            </span>
                                        </label>
                                        <div class="bg-white border rounded p-3">
                                            <div class="row g-3">
                                                <?php ($business_model = \App\Models\BusinessSetting::where('key', 'business_model')->first()); ?>
                                                <?php ($business_model = $business_model->value ? json_decode($business_model->value, true) : 0); ?>
                                                <div class="col-lg-6">
                                                    <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                                        <input type="checkbox" class="custom-control-input" value="1" name="subscription" id='subscription_id'
                                                            <?php echo e($business_model['subscription'] == 1 ? 'checked' : ''); ?>>
                                                        <label class="custom-control-label d-flex flex-column justify-content-between mb-0"  for="subscription_id">
                                                            <div>
                                                                <h5 class="mb-1">
                                                                    <?php echo e(translate('messages.Subscription')); ?>

                                                                </h5>
                                                                <p class="fs-12 mb-3" style="color: #677788 !important;"><?php echo e(translate('messages.By selecting subscription based business model restaurants can run business with you based on subscription package.')); ?></p>
                                                            </div>
                                                            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning" style="--bs-bg-opacity: 0.1;">
                                                                <span class="text-warning lh-1 fs-14">
                                                                    <i class="tio-info"></i>
                                                                </span>
                                                                <span>
                                                                    <?php echo e(translate('messages.To active subscription based business model 1st you need to add subscription package from')); ?>

                                                                    <a href="<?php echo e(route('admin.subscription.package_list')); ?>" class="font-semibold text-info text-underline"><?php echo e(translate('messages.Subscription Packages')); ?></a>.
                                                                </span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                                        <input type="checkbox" class="custom-control-input" value="1" name="commission" id="commission_id"
                                                            <?php echo e($business_model['commission'] == 1 ? 'checked' : ''); ?> >
                                                        <label class="custom-control-label d-flex flex-column justify-content-between mb-0"  for="commission_id">
                                                            <div>
                                                                <h5 class="mb-1">
                                                                   <?php echo e(translate('messages.Commission')); ?>

                                                               </h5>
                                                               <p class="fs-12 mb-3" style="color: #677788 !important;"><?php echo e(translate('messages.By selecting commission based business model restaurants can run business with you based on commission based payment per order.')); ?></p>
                                                            </div>
                                                            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info" style="--bs-bg-opacity: 0.1;">
                                                                <span class="text-info lh-1 fs-14">
                                                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/svg/bulb.svg')); ?>" class="svg" alt="">
                                                                </span>
                                                                <span>
                                                                    <?php echo e(translate('messages.To set different commission for commission based restaurant.')); ?>

                                                                    <?php echo e(translate('messages.Go to: ')); ?>

                                                                    <span class="font-semibold">
                                                                        <?php echo e(translate('messages.Restaurant List')); ?> >
                                                                        <?php echo e(translate('messages.Restaurant Details')); ?> >
                                                                        <?php echo e(translate('messages.Business Plan')); ?>

                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php ($admin_commission = \App\Models\BusinessSetting::where('key', 'admin_commission')->first()); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1" for="exampleFormControlInput1"><?php echo e(translate('messages.Default_commission')); ?> (%)
                                                <span class="text-danger">*</span>
                                                <span class="tio-info text-gray1 fs-16"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="<?php echo e(translate('messages.Set_up_Default_Commission_on_evrey_order._Admin_can_set_restaurant_wise_different_commission_rates_from_respective_restaurant_settings.')); ?>">
                                                </span>
                                            </label>
                                            <input type="number" name="admin_commission" class="form-control" id="admin_commission"
                                                value="<?php echo e($admin_commission ? $admin_commission?->value : 0); ?>" min="0" max="100" step="0.001"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php ($delivery_charge_comission = \App\Models\BusinessSetting::where('key', 'delivery_charge_comission')->first()); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1" for="exampleFormControlInput1"><?php echo e(translate('messages.Commission_on_Delivery_Charge')); ?> (%)
                                                <span class="text-danger">*</span>
                                                <span class="tio-info text-gray1 fs-16"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="<?php echo e(translate('messages.Set_a_default_Commission_Rate_for_freelance_deliverymen_(under_admin)_on_every_deliveryman')); ?>">
                                                </span>
                                            </label>
                                            <input type="number" name="admin_comission_in_delivery_charge" class="form-control" id="admin_comission_in_delivery_charge"
                                                min="0" max="100" step="0.001" value="<?php echo e($delivery_charge_comission ? $delivery_charge_comission?->value: 0); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mt-4" style="--bs-bg-opacity: 0.1;">
                                <span class="text-info lh-1 fs-14">
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/svg/bulb.svg')); ?>" class="svg" alt="">
                                </span>
                                <span>
                                    <?php echo e(translate('messages.To set different commission for commission based restaurant.')); ?>

                                    <?php echo e(translate('messages.Go to: ')); ?>

                                    <span class="font-semibold">
                                        <?php echo e(translate('messages.Restaurant')); ?> >
                                        <?php echo e(translate('messages.Restaurant List')); ?> >
                                        <?php echo e(translate('messages.Restaurant Details')); ?> >
                                        <?php echo e(translate('messages.Business Plan')); ?>

                                    </span>
                                </span>
                            </div>
                        </div>
                        <div class="card card-body p-12 p-xxl-20 mb-20" id="additional_charge_guide">
                            <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap mb-20">
                                <div class="flex-grow-1">
                                    <h4 class="mb-1"><?php echo e(translate('messages.Additional_Charge_Setup')); ?></h4>
                                    <p class="fs-12 mb-0"><?php echo e(translate('By switching this feature ON, Customer need to pay the amount you set')); ?>.</p>
                                </div>
                                <div class="flex-grow-1 max-w-360">
                                    <?php ($additional_charge_status = \App\Models\BusinessSetting::where('key', 'additional_charge_status')->first()); ?>
                                    <?php ($additional_charge_status = $additional_charge_status ? $additional_charge_status->value : 0); ?>
                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control m-0">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            <span><?php echo e(translate('messages.Status')); ?></span>
                                        </span>
                                        <input type="checkbox"
                                            data-id="additional_charge_status"
                                            data-type="toggle"
                                            data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/dm-tips-on.png')); ?>"
                                            data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/dm-tips-off.png')); ?>"
                                            data-title-on="<strong><?php echo e(translate('messages.Want_to_enable_additional_charge?')); ?></strong>"
                                            data-title-off="<strong><?php echo e(translate('messages.Want_to_disable_additional_charge?')); ?></strong>"
                                            data-text-on="<p><?php echo e(translate('messages.If_you_enable_this,_additional_charge_will_be_added_with_order_amount,_it_will_be_added_in_admin_wallet')); ?></p>"
                                            data-text-off="<p><?php echo e(translate('messages.If_you_disable_this,_additional_charge_will_not_be_added_with_order_amount.')); ?></p>"
                                            class="status toggle-switch-input dynamic-checkbox-toggle"
                                            value="1"
                                            name="additional_charge_status" id="additional_charge_status"
                                            <?php echo e($additional_charge_status == 1 ? 'checked' : ''); ?>>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="bg-light rounded-10 p-12 p-xxl-20">
                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <?php ($additional_charge_name = \App\Models\BusinessSetting::where('key', 'additional_charge_name')->first()); ?>
                                        <div class="form-group  mb-0">
                                            <label class="input-label d-flex align-items-center gap-1"
                                                for="additional_charge_name">
                                                <?php echo e(translate('messages.additional_charge_name')); ?>

                                                <span class="text-danger">*</span>
                                                <span class="tio-info text-gray1 fs-16"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="<?php echo e(translate('messages.Set_a_name_for_the_additional_charge,_e.g._“Processing_Fee”.')); ?>">
                                                </span>
                                            </label>

                                            <input type="text" name="additional_charge_name" class="form-control"
                                                id="additional_charge_name"  placeholder="<?php echo e(translate('messages.Ex:_Processing_Fee')); ?>"
                                                value="<?php echo e($additional_charge_name ? $additional_charge_name->value : ''); ?>" <?php echo e(isset($additional_charge_status) ? ' required' : 'readonly'); ?>>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php ($additional_charge = \App\Models\BusinessSetting::where('key', 'additional_charge')->first()); ?>
                                        <div class="form-group mb-0 ">
                                            <label class="input-label d-flex align-items-center gap-1"
                                                for="additional_charge">
                                                <?php echo e(translate('messages.charge_amount')); ?> (<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>)
                                                <span class="text-danger">*</span>
                                                <span class="tio-info text-gray1 fs-16"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="<?php echo e(translate('messages.Set_the_value_(amount)_customers_need_to_pay_as_additional_charge.')); ?>">
                                                </span>
                                            </label>
                                            <input type="number" name="additional_charge" class="form-control"
                                                id="additional_charge"  placeholder="<?php echo e(translate('messages.Ex:_10')); ?>"
                                                value="<?php echo e($additional_charge ? $additional_charge->value : 0); ?>"
                                                min="0" step=".01" <?php echo e(isset($additional_charge_status) ? 'required ' : 'readonly'); ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mt-4" style="--bs-bg-opacity: 0.1;">
                                <span class="text-info lh-1 fs-14">
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/svg/bulb.svg')); ?>" class="svg" alt="">
                                </span>
                                <span><?php echo e(translate('messages.Only admin will get the additional amount & customer must pay the amount.')); ?></span>
                            </div>
                        </div>
                        <div class="card card-body p-12 p-xxl-20" id="content_setup">
                            <div class="mb-20">
                                <h4 class="mb-1"><?php echo e(translate('messages.Content_setup')); ?></h4>
                                <p class="fs-12 mb-0"><?php echo e(translate('Setup your business time zone and format from here')); ?>.</p>
                            </div>
                            <div class="bg-light rounded-10 p-12 p-xxl-20">
                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <?php ($footer_text = \App\Models\BusinessSetting::where('key', 'footer_text')->first()); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1">
                                                <?php echo e(translate('copy_right_text')); ?>

                                                <span class="tio-info text-gray1 fs-16"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="<?php echo e(translate('make_visitors_aware_of_your_business‘s_rights_&_legal_information')); ?>">
                                                </span>
                                            </label>
                                            <textarea type="text" value="" name="footer_text" class="form-control" maxlength="100" placeholder="" rows="2"
                                                    required><?php echo e($footer_text->value ?? ''); ?></textarea>
                                            <div class="d-flex justify-content-end mt-1">
                                                <span class="text-body-light fs-12">0/100</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php ($cookies_text = \App\Models\BusinessSetting::where('key', 'cookies_text')->first()); ?>
                                        <div class="form-group mb-0">
                                            <label class="input-label d-flex align-items-center gap-1">
                                                <?php echo e(translate('Cookies_Text')); ?>

                                                <span class="tio-info text-gray1 fs-16"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="<?php echo e(translate('messages.make_visitors_aware_of_your_business‘s_rights_&_legal_information.')); ?>">
                                                </span>
                                            </label>
                                            <textarea type="text" value="" name="cookies_text" class="form-control" maxlength="100"
                                                    placeholder="<?php echo e(translate('messages.Ex_:_Cookies_Text')); ?> " rows="2" required><?php echo e($cookies_text->value ?? ''); ?></textarea>
                                            <div class="d-flex justify-content-end mt-1">
                                                <span class="text-body-light fs-12">0/100</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="footer-sticky mt-2">
                <div class="container-fluid">
                <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                    <button type="reset" id="reset_btn" class="btn btn--secondary min-w-120 location-reload"><?php echo e(translate('messages.Reset')); ?> </button>
                    <button type="<?php echo e(getEnvMode() != 'demo' ? 'submit' : 'button'); ?>" class="btn btn--primary call-demo">
                        <i class="tio-save"></i>
                        <?php echo e(translate('Save_Information')); ?>

                    </button>
                </div>
                </div>
            </div>
        </form>
    </div>
    <!-- Guidline Offcanvas Btn -->
<div class="d-flexgap-2 w-40px gap-2 bg-white position-fixed end-0 translate-middle-y pointer view-guideline-btn flex-column pt-3 px-2 justify-content-center offcanvas-trigger"
    data-toggle="offcanvas" data-target="#offcanvasSetupGuide">
    <span class="arrow bg-primary py-1 px-2 text-white rounded fs-12"><i class="tio-share-vs"></i></span>
    <span class="view-guideline-btn-text text-dark font-semibold pb-2 text-nowrap">
        <?php echo e(translate('View_Guideline')); ?>

    </span>
</div>

<!-- Guidline Offcanvas -->
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
<div class="custom-offcanvas" tabindex="-1" id="offcanvasSetupGuide" aria-labelledby="offcanvasSetupGuideLabel"
    style="--offcanvas-width: 500px">
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0"><?php echo e(translate('messages.Business Setup Guideline')); ?></h3>
            <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
        </div>
        <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#maintenance_mode_guide" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title"><?php echo e(translate('messages.Maintenance mode')); ?></span>
                    </button>
                    <a href="#maintenance_mode"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn"><?php echo e(translate('messages.Let’s Setup')); ?></a>
                </div>
                <div class="collapse show mt-3" id="maintenance_mode_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3"><?php echo e(translate('Maintenance mode')); ?></h5>
                            <p class="fs-12 mb-3">
                                <?php echo e(translate('messages.Turning on Maintenance mode will temporarily close your online store. So that the admin can do important updates or fixes.')); ?>

                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#basic_info_guide"
                        aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title"><?php echo e(translate('messages.Basic Information')); ?></span>
                    </button>
                    <a href="#basic_information"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn"><?php echo e(translate('messages.Let’s Setup')); ?></a>
                </div>
                <div class="collapse mt-3" id="basic_info_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3"><?php echo e(translate('Basic Information')); ?></h5>
                            <ul class="mb-0 fs-12">
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Company Name')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.The Company name often serves as the primary identifier for your business as a legal entity.')); ?>

                                </p>
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Email')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.A company email system often provides centralised management and archiving of business communication.')); ?>

                                </p>
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Phone')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.A phone number provides customers and partners with a direct and immediate way to reach your business for urgent inquiries, support needs, or quick questions.')); ?>

                                </p>
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Country')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.Country Name field when setting up a business is essential for a multitude of reasons, touching upon legal, operational, financial, and marketing aspects.')); ?>

                                </p>
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Description')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.This description is for describing the restaurant business. How vendors interact with the admin to run their business well.')); ?>

                                </p>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#general_settings_guide" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title"><?php echo e(translate('messages.General Settings')); ?></span>
                    </button>
                    <a href="#general_settings"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn"><?php echo e(translate('messages.Let’s Setup')); ?></a>
                </div>
                <div class="collapse mt-3" id="general_settings_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3"><?php echo e(translate('General Settings')); ?></h5>
                            <p class="fs-12 mb-3">
                                <?php echo e(translate('messages.General Setup is the foundational step, where you configure essential business details to set up the business.')); ?>

                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#country_picker_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title"><?php echo e(translate('messages.Country Picker')); ?></span>
                            </button>
                            <a href="#country_picker"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn"><?php echo e(translate('messages.Let’s Setup')); ?></a>
                        </div>
                        <div class="collapse mt-3" id="country_picker_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3"><?php echo e(translate('messages.Country Picker')); ?></h5>
                                    <p class="fs-12 mb-3">
                                        <?php echo e(translate('messages.Allows customers to select the appropriate country code when entering their phone number.')); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#business_model_guide"
                        aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title"><?php echo e(translate('messages.Business Model')); ?></span>
                    </button>
                    <a href="#business_model"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn"><?php echo e(translate('messages.Let’s Setup')); ?></a>
                </div>
                <div class="collapse mt-3" id="business_model_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3"><?php echo e(translate('Business Model')); ?></h5>
                            <ul class="mb-0 fs-12">
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Subscription-based model')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.A subscription-based business model allows customers or vendors to access specific features, services, or system functionalities by paying a recurring fee (monthly, quarterly, or yearly). Instead of one-time payments, users remain active as long as their subscription is valid.')); ?>

                                </p>
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Commission-based model')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.A commission-based business model allows the platform to earn revenue by charging a predefined percentage amount from each successful transaction completed through the system. The commission is automatically deducted from the order value before the remaining amount is settled with the vendor or service provider.')); ?>

                                </p>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#additional_charge_guide" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title"><?php echo e(translate('messages.Additional Charge Setup')); ?></span>
                    </button>
                    <a href="#additional_charge_guide"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn"><?php echo e(translate('messages.Let’s Setup')); ?></a>
                </div>
                <div class="collapse mt-3" id="additional_charge_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3"><?php echo e(translate('Additional Charge Setup')); ?></h5>
                            <p class="fs-12 mb-3">
                                <?php echo e(translate('messages.The Additional Charge Setup allows the admin to configure extra fees that will be applied to customer orders based on predefined conditions. These charges are added to the order total automatically during checkout and are visible to both customers and vendors.')); ?>

                            </p>
                        </div>
                    </div>
                </div>
            </div>



            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#content_setup_guide"
                        aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title"><?php echo e(translate('messages.Content Setup')); ?></span>
                    </button>
                    <a href="#content_setup"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn"><?php echo e(translate('messages.Let’s Setup')); ?></a>
                </div>
                <div class="collapse mt-3" id="content_setup_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3"><?php echo e(translate('Content Setup')); ?></h5>
                            <ul class="mb-0 fs-12">
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Copyright text')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.This is a short statement that shows your company owns the content on your website. It usually includes the copyright symbol (©), the year, and your company name.')); ?>

                                </p>
                                <li class="font-semibold">
                                    <?php echo e(translate('messages.Cookies Text')); ?>

                                </li>
                                <p class="mt-2 mb-3">
                                    <?php echo e(translate('messages.This is a short message shown on the website to let visitors know that the site uses cookies to collect information and improve their browsing experience.')); ?>

                                </p>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>


    
    <div class="modal fade" id="currency-warning-modal">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img width="80" src="<?php echo e(dynamicAsset('assets/admin/img/modal/currency.png')); ?>" class="mb-20">
                                <h5 class="modal-title"></h5>
                            </div>
                            <div class="text-center" >
                                <h3 > <?php echo e(translate('Are_you_sure_to_change_the_currency_?')); ?></h3>
                                <div > <p><?php echo e(translate('If_you_enable_this_currency,_you_must_active_at_least_one_digital_payment_method_that_supports_this_currency._Otherwise_customers_cannot_pay_via_digital_payments_from_the_app_and_websites._And_Also_restaurants_cannot_pay_you_digitally')); ?></h3></p></div>
                            </div>

                            <div class="text-center mb-4" >
                                <a class="text--underline" href="<?php echo e(route('admin.business-settings.payment-method')); ?>"> <?php echo e(translate('Go_to_payment_method_settings.')); ?></a>
                            </div>
                        </div>

                        <div class="btn--container justify-content-center">
                            <button data-dismiss="modal" id="confirm-currency-change" class="btn btn--cancel min-w-120" ><?php echo e(translate("Cancel")); ?></button>
                            <button data-dismiss="modal"   type="button"  class="btn btn--primary min-w-120"><?php echo e(translate('OK')); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="maintenance-off-mode-modal">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <form method="post" action="<?php echo e(route('admin.maintenance-mode')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="maintenance_mode_off" value="1">
                    <div class="modal-body pb-5 pt-0">
                        <div class="max-349 mx-auto mb-20">
                            <div>
                                <div class="text-center">
                                    <img width="80" src="<?php echo e(dynamicAsset('assets/admin/img/modal/maintenance-off.png')); ?>" class="mb-20">
                                    <h5 class="modal-title"><?php echo e(translate('Are you sure you?')); ?></h5>
                                </div>
                                <div class="text-center" >
                                    
                                    <div > <p><?php echo e(translate('Do you want to turn off Maintenance mode? Turning it off will activate all systems that were deactivated.')); ?></h3></p></div>
                                </div>
                            </div>

                            <div class="btn--container justify-content-center">
                                <button data-dismiss="modal" type="button" class="btn btn--cancel min-w-120" ><?php echo e(translate("Cancel")); ?></button>
                                <button  type="submit"  class="btn btn--primary min-w-120"><?php echo e(translate('Yes')); ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="maintenance-mode-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header pt-3">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <form method="post" action="<?php echo e(route('admin.maintenance-mode')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body pt-3 px-0">
                        <?php echo csrf_field(); ?>
                        <div class="d-flex flex-column gap-4">
                            <div class="px-4">
                                <div class="row mb-4">
                                    <div class="col-xl-4">
                                        <h5 class="mb-2"><?php echo e(translate('Select System')); ?></h5>
                                        <p><?php echo e(translate('Select the systems you want to temporarily deactivate for maintenance')); ?></p>
                                    </div>
                                    <div class="col-xl-8">
                                        <div class="border p-3 p-sm-4 rounded">
                                            <div class="d-flex flex-wrap gap-4">
                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input system-checkbox" name="all_system" type="checkbox"
                                                           <?php echo e(in_array('admin_panel', $selectedMaintenanceSystem) &&
                                                                   in_array('restaurant_panel', $selectedMaintenanceSystem) &&
                                                                   in_array('user_mobile_app', $selectedMaintenanceSystem) &&
                                                                   in_array('user_web_app', $selectedMaintenanceSystem) &&
                                                                   in_array('react_website', $selectedMaintenanceSystem) &&
                                                                   in_array('deliveryman_app', $selectedMaintenanceSystem) &&
                                                                   in_array('restaurant_app', $selectedMaintenanceSystem) ? 'checked' :''); ?>

                                                           id="allSystem">
                                                    <label class="form-check-label" for="allSystem"><?php echo e(translate('All System')); ?></label>
                                                </div>

                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input system-checkbox" name="restaurant_panel" type="checkbox"
                                                           <?php echo e(in_array('restaurant_panel', $selectedMaintenanceSystem) ? 'checked' :''); ?>

                                                           id="restaurant_panel">
                                                    <label class="form-check-label" for="restaurant_panel"><?php echo e(translate('Restaurant Panel')); ?></label>
                                                </div>
                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input system-checkbox" name="user_mobile_app" type="checkbox"
                                                           <?php echo e(in_array('user_mobile_app', $selectedMaintenanceSystem) ? 'checked' :''); ?>

                                                           id="user_mobile_app">
                                                    <label class="form-check-label" for="user_mobile_app"><?php echo e(translate('User Mobile App')); ?></label>
                                                </div>
                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input system-checkbox" name="user_web_app" type="checkbox"
                                                           <?php echo e(in_array('user_web_app', $selectedMaintenanceSystem) ? 'checked' :''); ?>

                                                           id="user_web_app">
                                                    <label class="form-check-label" for="user_web_app"><?php echo e(translate('User Website')); ?></label>
                                                </div>
                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input system-checkbox" name="react_website" type="checkbox"
                                                           <?php echo e(in_array('react_website', $selectedMaintenanceSystem) ? 'checked' :''); ?>

                                                           id="react_website">
                                                    <label class="form-check-label" for="react_website"><?php echo e(translate('React Website')); ?></label>
                                                </div>
                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input system-checkbox" name="deliveryman_app" type="checkbox"
                                                           <?php echo e(in_array('deliveryman_app', $selectedMaintenanceSystem) ? 'checked' :''); ?>

                                                           id="deliveryman_app">
                                                    <label class="form-check-label" for="deliveryman_app"><?php echo e(translate('Deliveryman App')); ?></label>
                                                </div>
                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input system-checkbox" name="restaurant_app" type="checkbox"
                                                           <?php echo e(in_array('restaurant_app', $selectedMaintenanceSystem) ? 'checked' :''); ?>

                                                           id="restaurant_app">
                                                    <label class="form-check-label" for="restaurant_app"><?php echo e(translate('Restaurant App')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-xl-4">
                                        <h5 class="mb-2"><?php echo e(translate('Maintenance Date')); ?> & <?php echo e(translate('Time')); ?></h5>
                                        <p><?php echo e(translate('Choose the maintenance mode duration for your selected system.')); ?></p>
                                    </div>
                                    <div class="col-xl-8">
                                        <div class="border p-3 p-sm-4 rounded">
                                            <div class="d-flex flex-wrap gap-4 mb-3">
                                                <div class="form-check form--check">
                                                    <input class="form-check-input" type="radio" name="maintenance_duration"
                                                           <?php echo e(isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'one_day' ? 'checked' : ''); ?>

                                                           value="one_day" id="one_day">
                                                    <label class="form-check-label opacity-100" for="one_day"><?php echo e(translate('For 24 Hours')); ?></label>
                                                </div>
                                                <div class="form-check form--check">
                                                    <input class="form-check-input" type="radio" name="maintenance_duration"
                                                           <?php echo e(isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'one_week' ? 'checked' : ''); ?>

                                                           value="one_week" id="one_week">
                                                    <label class="form-check-label opacity-100" for="one_week"><?php echo e(translate('For 1 Week')); ?></label>
                                                </div>
                                                <div class="form-check form--check">
                                                    <input class="form-check-input" type="radio" name="maintenance_duration"
                                                           <?php echo e(isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'until_change' ? 'checked' : ''); ?>

                                                           value="until_change" id="until_change">
                                                    <label class="form-check-label opacity-100" for="until_change"><?php echo e(translate('Until I change')); ?></label>
                                                </div>
                                                <div class="form-check form--check">
                                                    <input class="form-check-input" type="radio" name="maintenance_duration"
                                                           <?php echo e(isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'customize' ? 'checked' : ''); ?>

                                                           value="customize" id="customize">
                                                    <label class="form-check-label opacity-100" for="customize"><?php echo e(translate('Customize')); ?></label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label"><?php echo e(translate('Start Date')); ?></label>
                                                    <input type="datetime-local" class="form-control h-40" name="start_date" id="startDate"
                                                           value="<?php echo e(old('start_date', $selectedMaintenanceDuration['start_date'] ?? '')); ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label"><?php echo e(translate('End Date')); ?></label>
                                                    <input type="datetime-local" class="form-control h-40" name="end_date" id="endDate"
                                                           value="<?php echo e(old('end_date', $selectedMaintenanceDuration['end_date'] ?? '')); ?>" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <small id="dateError" class="form-text text-danger" style="display: none;"><?php echo e(translate('Start date cannot be greater than end date.')); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="advanceFeatureButtonDiv">
                            <div class="px-4">
                                <a type="button" href="#" id="advanceFeatureToggle" class="text--base fw-semibold d-block maintenance-advance-feature-button"><?php echo e(translate('Advance Feature')); ?> <i class="tio-arrow-drop-down-circle-outlined"></i> </a>
                            </div>
                        </div>
                        <div class="px-4">
                            <div class="row" id="advanceFeatureSection" style="display: none;">
                                <div class="col-xl-4">
                                    <h5 class="mb-2"><?php echo e(translate('Maintenance Massage')); ?></h5>
                                    <p><?php echo e(translate('Select & type what massage you want to see your selected system when maintenance mode is active.')); ?></p>
                                </div>
                                <div class="col-xl-8">
                                    <div class="border rounded p-3">
                                        <div class="form-group">
                                            <label class="form-label"><?php echo e(translate('Contact us through')); ?></label>
                                            <div class="d-flex flex-wrap gap-5 mb-3">
                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input" type="checkbox" name="business_number"
                                                           <?php echo e(isset($selectedMaintenanceMessage['business_number']) && $selectedMaintenanceMessage['business_number'] == 1 ? 'checked' : ''); ?>

                                                           id="businessNumber">
                                                    <label class="form-check-label" for="businessNumber"><?php echo e(translate('Business Number')); ?></label>
                                                </div>
                                                <div class="form-check form--check m-0">
                                                    <input class="form-check-input" type="checkbox" name="business_email"
                                                           <?php echo e(isset($selectedMaintenanceMessage['business_email']) && $selectedMaintenanceMessage['business_email'] == 1 ? 'checked' : ''); ?>

                                                           id="businessEmail">
                                                    <label class="form-check-label" for="businessEmail"><?php echo e(translate('Business Email')); ?></label>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <label class="form-label"><?php echo e(translate('Message Title')); ?></label>
                                            <input type="text" class="form-control h-40" name="maintenance_message" placeholder="<?php echo e(translate('We are Working On Something Special!')); ?>"
                                                   maxlength="200" value="<?php echo e($selectedMaintenanceMessage['maintenance_message'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group mt-3">
                                            <label class="form-label"><?php echo e(translate('Message Details')); ?></label>
                                            <input type="text" class="form-control h-40" name="message_body" placeholder="<?php echo e(translate('We are Working On Something Special!')); ?>"
                                                   maxlength="200" value="<?php echo e($selectedMaintenanceMessage['message_body'] ?? ''); ?>">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex ml-5 mt-4">
                                        <a type="button" href="#" id="seeLessToggle" class="text--base fw-semibold d-block mb-3 maintenance-advance-feature-button"><?php echo e(translate('Advance Feature')); ?> <i class="tio-arrow-drop-up-circle-outlined" ></i> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex flex-wrap gap-3 justify-content-end">
                            <button data-dismiss="modal" class="btn btn--cancel" data-bs-dismiss="modal"><?php echo e(translate('Cancel')); ?></button>
                            <button type="<?php echo e(getEnvMode()!='demo'?'submit':'button'); ?>" class="btn btn--primary <?php echo e(getEnvMode() =='demo'? 'demo_check':''); ?>"><?php echo e(translate('Active')); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value); ?>&libraries=places,marker&v=3.61">
    </script>
    <script>
        "use strict";

        $(document).on('click', '.demo_check', function (event) {
            toastr.warning('<?php echo e(translate('Sorry! You can not enable maintenance mode in demo!')); ?>', {
                CloseButton: true,
                ProgressBar: true
            });
            event.preventDefault();
        });


        $(document).on('click', '.maintenance-mode', function (event) {
            event.preventDefault();
            $('#maintenance-mode-modal').modal('show');

        });
        $(document).on('click', '.turn_off_maintenance_mode', function (event) {
            event.preventDefault();
            $('#maintenance-off-mode-modal').modal('show');
        });


        $('#advanceFeatureToggle').click(function (event) {
            event.preventDefault();
            $('#advanceFeatureSection').show();
            $('#advanceFeatureButtonDiv').hide();
        });

        $('#seeLessToggle').click(function (event) {
            event.preventDefault();
            $('#advanceFeatureSection').hide();
            $('#advanceFeatureButtonDiv').show();
        });

        function readURL(input, viewer) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + viewer).attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function() {
            readURL(this, 'viewer');
        });

        $("#favIconUpload").change(function() {
            readURL(this, 'iconViewer');
        });

        function initAutocomplete() {

            const mapId = "<?php echo e(\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value); ?>";
            var myLatLng = {
                lat: <?php echo e($default_location ? $default_location['lat'] : '-33.8688'); ?>,
                lng: <?php echo e($default_location ? $default_location['lng'] : '151.2195'); ?>

            };
            const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                center: {
                    lat: <?php echo e($default_location ? $default_location['lat'] : '-33.8688'); ?>,
                    lng: <?php echo e($default_location ? $default_location['lng'] : '151.2195'); ?>

                },
                mapId: mapId,
                zoom: 13,
                mapTypeId: "roadmap",
            });

            const { AdvancedMarkerElement } = google.maps.marker;

            var marker = new AdvancedMarkerElement({
                position: myLatLng,
                map: map,
            });

            marker.setMap(map);
            var geocoder = geocoder = new google.maps.Geocoder();
            google.maps.event.addListener(map, 'click', function(mapsMouseEvent) {
                var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                var coordinates = JSON.parse(coordinates);
                var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
                marker.position = latlng;
                // marker.setPosition(latlng);
                map.panTo(latlng);

                document.getElementById('latitude').value = coordinates['lat'];
                document.getElementById('longitude').value = coordinates['lng'];


                geocoder.geocode({
                    'latLng': latlng
                }, function(results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        if (results[1]) {
                            document.getElementById('address').value = results[1].formatted_address;
                        }
                    }
                });
            });
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
            let markers = [];
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
                    return;
                }
                markers.forEach(m => m.map = null);
                // markers.forEach((marker) => {
                //     marker.setMap(null);
                // });
                markers = [];
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }
                    var mrkr = new AdvancedMarkerElement({
                        map,
                        title: place.name,
                        position: place.geometry.location,
                    });
                    google.maps.event.addListener(mrkr, "click", function(event) {
                        document.getElementById('latitude').value = this.position.lat();
                        document.getElementById('longitude').value = this.position.lng();
                    });

                    markers.push(mrkr);

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
        }

        $(document).on('ready', function() {
            initAutocomplete();
            <?php ($country = \App\Models\BusinessSetting::where('key', 'country')->first()); ?>

            <?php if($country): ?>
            $("#country option[value='<?php echo e($country->value); ?>']").attr('selected', 'selected').change();
            <?php endif; ?>

        });

        $(document).on("keydown", "input", function(e) {
            if (e.which === 13) e.preventDefault();
        });
        $(document).ready(function() {
            let selectedCurrency = "<?php echo e($currency_code ? $currency_code->value : 'USD'); ?>";
            let currencyConfirmed = false;
            let updatingCurrency = false;

            $("#change_currency").change(function() {
                if (!updatingCurrency) check_currency($(this).val());
            });

            $("#confirm-currency-change").click(function() {
                currencyConfirmed = true;
                update_currency(selectedCurrency);
                $('#currency-warning-modal').modal('hide');
            });

            function check_currency(currency) {
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    url: "<?php echo e(route('admin.system_currency')); ?>",
                    method: 'GET',
                    data: { currency: currency },
                    success: function(response) {
                        if (response.data) {
                            $('#currency-warning-modal').modal('show');
                        } else {
                            update_currency(currency);
                        }
                    }
                });
            }

            function update_currency(currency) {
                if (currencyConfirmed) {
                    updatingCurrency = true;
                    $("#change_currency").val(currency).trigger('change');
                    updatingCurrency = false;
                    currencyConfirmed = false;
                }
            }
        });



        $(document).ready(function() {

            $('#advanceFeatureToggle').click(function (event) {
                event.preventDefault();
                $('#advanceFeatureSection').show();
                $('#advanceFeatureButtonDiv').hide();
            });

            $('#seeLessToggle').click(function (event) {
                event.preventDefault();
                $('#advanceFeatureSection').hide();
                $('#advanceFeatureButtonDiv').show();
            });

            $('#allSystem').change(function () {
                var isChecked = $(this).is(':checked');
                $('.system-checkbox').prop('checked', isChecked);
            });

            $('.system-checkbox').not('#allSystem').change(function () {
                if (!$(this).is(':checked')) {
                    $('#allSystem').prop('checked', false);
                } else {
                    if ($('.system-checkbox').not('#allSystem').length === $('.system-checkbox:checked').not('#allSystem').length) {
                        $('#allSystem').prop('checked', true);
                    }
                }
            }).trigger('change');;

            $(document).ready(function () {
                var startDate = $('#startDate');
                var endDate = $('#endDate');
                var dateError = $('#dateError');

                function updateDatesBasedOnDuration(selectedOption) {
                    if (selectedOption === 'one_day' || selectedOption === 'one_week') {
                        var now = new Date();
                        var timezoneOffset = now.getTimezoneOffset() * 60000;
                        var formattedNow = new Date(now.getTime() - timezoneOffset).toISOString().slice(0, 16);

                        if (selectedOption === 'one_day') {
                            var end = new Date(now);
                            end.setDate(end.getDate() + 1);
                        } else if (selectedOption === 'one_week') {
                            var end = new Date(now);
                            end.setDate(end.getDate() + 7);
                        }

                        var formattedEnd = new Date(end.getTime() - timezoneOffset).toISOString().slice(0, 16);

                        startDate.val(formattedNow).prop('readonly', false).prop('required', true);
                        endDate.val(formattedEnd).prop('readonly', false).prop('required', true);
                        startDate.closest('div').css('display', 'block');
                        endDate.closest('div').css('display', 'block');
                        dateError.hide();
                    } else if (selectedOption === 'until_change') {
                        startDate.val('').prop('readonly', true).prop('required', false);
                        endDate.val('').prop('readonly', true).prop('required', false);
                        startDate.closest('div').css('display', 'none');
                        endDate.closest('div').css('display', 'none');
                        dateError.hide();
                    } else if (selectedOption === 'customize') {
                        startDate.prop('readonly', false).prop('required', true);
                        endDate.prop('readonly', false).prop('required', true);
                        startDate.closest('div').css('display', 'block');
                        endDate.closest('div').css('display', 'block');
                        dateError.hide();
                    }
                }

                function validateDates() {
                    var start = new Date(startDate.val());
                    var end = new Date(endDate.val());
                    if (start >= end) {
                        dateError.show();
                        startDate.val('');
                        endDate.val('');
                    } else {
                        dateError.hide();
                    }
                }

                var selectedOption = $('input[name="maintenance_duration"]:checked').val();
                updateDatesBasedOnDuration(selectedOption);

                $('input[name="maintenance_duration"]').change(function () {
                    var selectedOption = $(this).val();
                    updateDatesBasedOnDuration(selectedOption);
                });

                $('#startDate, #endDate').change(function () {
                    $('input[name="maintenance_duration"][value="customize"]').prop('checked', true);
                    startDate.prop('readonly', false).prop('required', true);
                    endDate.prop('readonly', false).prop('required', true);
                    validateDates();
                });
                $('#subscription_id, #commission_id').on('change', function () {
                let subscription = $('#subscription_id').is(':checked');
                let commission = $('#commission_id').is(':checked');

                if (!subscription && !commission) {
                    toastr.error("<?php echo e(translate('messages.At least one business model must be active.')); ?>");
                    $(this).prop('checked', true);
                    return;
                }
            });
            });

        });
        $(".collapse-div-toggler").on('change', function () {
            if ($(this).val() == '0') {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideDown();
            } else {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideUp();
            }
        });

        $(window).on('load', function () {
            $('.collapse-div-toggler').each(function () {
                if ($(this).prop('checked') == true && $(this).val() == '0') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').show();
                } else if ($(this).prop('checked') == true && $(this).val() == '1') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').hide();
                }
            });
        })


        $('.offcanvas-close-btn').on('click', function (e) {
            e.preventDefault();
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 100
            }, 500);
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/business-settings/business-index.blade.php ENDPATH**/ ?>