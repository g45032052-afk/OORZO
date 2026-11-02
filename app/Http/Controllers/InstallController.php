<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Madnest\Madzipper\Facades\Madzipper;

class InstallController extends Controller
{
    public function step0()
    {
        return view('installation.step0');
    }


    public function step1(Request $request)
    {
        if (Hash::check('step_1', $request['token'])) {
            $permission = Helpers::system_permission_check();
            $fileChecks = Helpers::system_file_checks();
            $phpVersion = number_format((float)phpversion(), 2, '.', '');
            return view('installation.step1', compact('permission', 'fileChecks', 'phpVersion'));
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    public function step2(Request $request)
    {
        if (Hash::check('step_2', $request['token'])) {
            return to_route('step3', ['token' => bcrypt('step_3')]);
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    public function step3(Request $request)
    {
        if (Hash::check('step_3', $request['token'])) {
            return view('installation.step3');
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    public function step4(Request $request)
    {
        if (Hash::check('step_4', $request['token'])) {
            return view('installation.step4');
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    public function step5(Request $request)
    {
        if (Hash::check('step_5', $request['token'])) {
            return view('installation.step5');
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    public function system_settings(Request $request)
    {
        if (!Hash::check('step_6', $request['token'])) {
            session()->flash('error', 'Access denied!');
            return to_route('step0');
        }

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'same:confirm_password'],
            'confirm_password' => 'required',
        ]);
        if ($validator->fails()) {
            session()->flash('error', 'Confirm password does not match!');
            return back();
        }

        DB::table('admins')->insertOrIgnore([
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'role_id' => 1,
            'password' => bcrypt($request['password']),
            'phone' => $request['phone_code'].$request['phone'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('business_settings')->where(['key' => 'business_name'])->update([
            'value' => $request['business_name']
        ]);


        Helpers::insert_business_settings_key('system_language','[{"id":1,"direction":"ltr","code":"en","status":1,"default":true}]');

        //version 7.0
        Helpers::insert_data_settings_key('admin_login_url', 'login_admin' ,'admin');
        Helpers::insert_data_settings_key('admin_employee_login_url', 'login_admin_employee' ,'admin_employee');
        Helpers::insert_data_settings_key('restaurant_login_url', 'login_restaurant' ,'restaurant');
        Helpers::insert_data_settings_key('restaurant_employee_login_url', 'login_restaurant_employee' ,'restaurant_employee');

        Helpers::insert_business_settings_key('take_away', '1');
        Helpers::insert_business_settings_key('repeat_order_option', '1');
        Helpers::insert_business_settings_key('home_delivery', '1');
        Helpers::insert_business_settings_key('country_picker_status', '1');
        Helpers::insert_business_settings_key('manual_login_status', '1');

        // 9.3 - every distance in the panel and the APIs is read through this key. Helpers
        // falls back to km when it is missing, but the Business Settings form needs a real
        // row to preselect, so the default is written once here and left alone afterwards.
        Helpers::insert_business_settings_key('distance_unit', 'km');


        // Leaving install mode. copy() returns false rather than raising, and a site that
        // cannot get its normal provider back keeps showing the installer.
        $previousRouteServiceProvier = base_path('app/Providers/RouteServiceProvider.php');
        $newRouteServiceProvier = base_path('app/Providers/RouteServiceProvider.txt');

        if (! is_readable($newRouteServiceProvier) || ! copy($newRouteServiceProvier, $previousRouteServiceProvier)) {
            // Also logged - step6 is the last screen, where a flash message is easy to miss.
            session()->flash('error', 'Installation finished but normal routing could not be restored: check that app/Providers/RouteServiceProvider.txt exists and that app/Providers/ is writable.');
            info('InstallController: could not restore RouteServiceProvider.php from RouteServiceProvider.txt');
        }
        //sleep(5);

        Helpers::remove_dir('storage/app/public');
        Storage::disk('public')->makeDirectory('/');

        try {
            Madzipper::make('installation/backup/public.zip')->extractTo('storage/app');
        }catch (\Exception $exception){
            info($exception);
        }

        return view('installation.step6');
    }

    public function database_installation(Request $request)
    {
        $token = $request->input('token');
        if (!is_string($token) || !Hash::check('step_4', $token)) {
            session()->flash('error', 'Access denied!');
            return to_route('step0');
        }

        if (self::check_database_connection($request->DB_HOST, $request->DB_DATABASE, $request->DB_USERNAME, $request->DB_PASSWORD)) {

            $key = base64_encode(random_bytes(32));
            $appUrl = URL::to('/');
            $hostDomain = parse_url($appUrl, PHP_URL_HOST) ?: '';
            $hostBaseDomain = Helpers::host_base_domain($hostDomain);
            $publicIp = $request->server('SERVER_ADDR')
                ?: (filter_var($hostDomain, FILTER_VALIDATE_IP) ? $hostDomain : '');
            $output = 'APP_NAME=stackfood' . time() .
                'APP_ENV=live
                    APP_KEY=base64:' . $key . '
                    APP_DEBUG=false
                    APP_INSTALL=true
                    APP_LOG_LEVEL=debug
                    APP_MODE=live
                    APP_URL=' . $appUrl . '
                    APP_HOST_DOMAIN=' . $hostDomain . '
                    APP_HOST_BASE_DOMAIN=' . $hostBaseDomain . '
                    APP_PUBLIC_IP=' . $publicIp . '

                    DB_CONNECTION=mysql
                    DB_HOST=' . $request->DB_HOST . '
                    DB_PORT=3306
                    DB_DATABASE=' . $request->DB_DATABASE . '
                    DB_USERNAME=' . $request->DB_USERNAME . '
                    DB_PASSWORD="' . $request->DB_PASSWORD . '"

                    BROADCAST_DRIVER=log
                    CACHE_DRIVER=database
                    SESSION_DRIVER=file
                    SESSION_LIFETIME=120
                    QUEUE_DRIVER=sync

                    REDIS_HOST=127.0.0.1
                    REDIS_PASSWORD=null
                    REDIS_PORT=6379

                    PUSHER_APP_ID=
                    PUSHER_APP_KEY=
                    PUSHER_APP_SECRET=
                    PUSHER_APP_CLUSTER=mt1

                    SOFTWARE_VERSION=9.3
                    REACT_APP_KEY=43218516
                    ';
            $file = fopen(base_path('.env'), 'w');
            fwrite($file, $output);
            fclose($file);

            $path = base_path('.env');
            if (file_exists($path)) {
                return to_route('step4', ['token' => $request['token']]);
            } else {
                session()->flash('error', 'Database error!');
                return to_route('step3', ['token' => bcrypt('step_3')]);
            }
        } else {
            session()->flash('error', 'Database host error!');
            return to_route('step3', ['token' => bcrypt('step_3')]);
        }
    }

    public function import_sql(Request $request)
    {
        $token = $request->input('token');
        if (!is_string($token) || !Hash::check('step_5', $token)) {
            session()->flash('error', 'Access denied!');
            return to_route('step0');
        }

        try {
            $sql_path = base_path('installation/backup/database.sql');
            DB::unprepared(file_get_contents($sql_path));
            // version_7.9.1
            Artisan::call('cache:table');
            return to_route('step5', ['token' => bcrypt('step_5')]);
        } catch (\Exception $exception) {
            session()->flash('error', 'Your database is not clean, do you want to clean database then import?');
            return back();
        }
    }

    public function force_import_sql(Request $request)
    {
        $token = $request->input('token');
        if (!is_string($token) || !Hash::check('step_5', $token)) {
            session()->flash('error', 'Access denied!');
            return to_route('step0');
        }

        try {
            Artisan::call('db:wipe', ['--force' => true]);
            $sql_path = base_path('installation/backup/database.sql');
            DB::unprepared(file_get_contents($sql_path));
            // version_7.9.1
            Artisan::call('cache:table');
            return to_route('step5', ['token' => bcrypt('step_5')]);
        } catch (\Exception $exception) {
            session()->flash('error', 'Check your database permission!');
            return back();
        }
    }

    function check_database_connection($db_host = "", $db_name = "", $db_user = "", $db_pass = ""): bool
    {
        try {
            if (@mysqli_connect($db_host, $db_user, $db_pass, $db_name)) {
                return true;
            } else {
                return false;
            }
        }catch(\Exception $exception){
            return false;
        }
    }
}
