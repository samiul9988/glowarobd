<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Nwidart\Modules\Facades\Module;

class BusinessSettingsController extends Controller
{
    public function general_setting(Request $request)
    {
    	return view('backend.setup_configurations.general_settings');
    }
    public function notification_setting(Request $request)
    {
    	return view('backend.setup_configurations.notification_settings');
    }
    public function cloudflare_setting(Request $request)
    {
    	return view('backend.setup_configurations.cloudflare_settings');
    }
    public function courier_success_rate_setting(Request $request)
    {
    	return view('backend.setup_configurations.courier_success_rate_settings');
    }
    public function rokomari_setting(Request $request)
    {
    	return view('backend.setup_configurations.rokomari_settings');
    }

    public function activation(Request $request)
    {
    	return view('backend.setup_configurations.activation');
    }

    public function social_login(Request $request)
    {
        return view('backend.setup_configurations.social_login');
    }

    public function smtp_settings(Request $request)
    {
        return view('backend.setup_configurations.smtp_settings');
    }

    public function google_analytics(Request $request)
    {
        return view('backend.setup_configurations.google_configuration.google_analytics');
    }

    public function google_tagmanager(Request $request)
    {
        return view('backend.setup_configurations.google_configuration.google_tagmanager');
    }

    public function google_recaptcha(Request $request)
    {
        return view('backend.setup_configurations.google_configuration.google_recaptcha');
    }

    public function google_map(Request $request) {
        return view('backend.setup_configurations.google_configuration.google_map');
    }

    public function google_firebase(Request $request) {
        return view('backend.setup_configurations.google_configuration.google_firebase');
    }

    public function onesignal(Request $request) {
        return view('backend.setup_configurations.onesignal.onesignal');
    }

    public function facebook_chat(Request $request)
    {
        return view('backend.setup_configurations.facebook_chat');
    }

    public function facebook_comment(Request $request)
    {
        return view('backend.setup_configurations.facebook_configuration.facebook_comment');
    }

    public function payment_method(Request $request)
    {
        return view('backend.setup_configurations.payment_method');
    }

    public function file_system(Request $request)
    {
        return view('backend.setup_configurations.file_system');
    }

    /**
     * Home Category Store method
     */
    public function store_home_category(Request $request){
        try {
            $this->validate($request, [
                "home_categories" => "required|array",
                "collection_designs" => "required|array",
                "home_categories.*" => "integer",
                "collection_designs.*" => "integer",
            ]);

            $data = array_map(function ($category, $design) {
                return ["cid" => $category, "did" => $design];
            }, $request->input("home_categories"), $request->input("collection_designs"));

            BusinessSetting::where("type", "home_categories")->update([
                "value" => json_encode($data)
            ]);

            Cache::flush();
            flash(("Settings updated successfully"))->success();
            return back();
        } catch (\Exception $e) {
            flash(("Request failed"))->error();
            return back();
        }

    }

    public function store_home_category_app(Request $request){
        try {
            $this->validate($request, [
                "home_categories" => "required|array",
                "collection_designs" => "required|array",
                "home_categories.*" => "integer",
                "collection_designs.*" => "integer",
            ]);

            $data = array_map(function ($category, $design) {
                return ["cid" => $category, "did" => $design];
            }, $request->input("home_categories"), $request->input("collection_designs"));

            BusinessSetting::updateOrCreate(
                ["type" => "home_categories_app"],
                ["value" => json_encode($data)]
            );

            Cache::flush();
            flash(("Settings updated successfully"))->success();
            return back();
        } catch (\Exception $e) {
            flash(($e->getMessage()))->error();
            return back();
        }

    }

    public function updateShortcuts(Request $request)
    {
        $payload = collect($request->labels)->map(function($label, $index) use ($request) {
            if(trim($label) != '') {
                return [
                    'label' => $label,
                    'link' => $request->links[$index] ?? '#',
                ];
            }
        })->filter()->values()->all();

        BusinessSetting::updateOrCreate(
            ['type' => 'quick_shortcuts'],
            ['value' => json_encode($payload)]
        );
        Cache::flush();
        flash(("Settings updated successfully"))->success();
        return back();
    }

    /**
     * Update the API key's for payment methods.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function payment_method_update(Request $request)
    {
        // dd($request->all());
        foreach ($request->types as $key => $type) {
                $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', $request->payment_method.'_sandbox')->first();
        if($business_settings != null){
            if ($request->has($request->payment_method.'_sandbox')) {
                if($request->payment_method === 'bkash'){
                    write_env([
                        'BKASH_SANDBOX' => true,
                        'BKASH_CHECKOUT_TOKEN' => null,
                        'BKASH_CHECKOUT_TOKEN_EXPIRE' => null,
                    ]);
                }
                $business_settings->value = 1;
                $business_settings->save();
            }
            else{
                if($request->payment_method === 'bkash'){
                    write_env([
                        'BKASH_SANDBOX' => false,
                        'BKASH_CHECKOUT_TOKEN' => null,
                        'BKASH_CHECKOUT_TOKEN_EXPIRE' => null,
                    ]);
                }
                $business_settings->value = 0;
                $business_settings->save();
            }
        }

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    /**
     * Update the API key's for GOOGLE analytics.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function google_analytics_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
            if ($type == 'TRACKING_ID') {
                BusinessSetting::updateOrCreate(
                    ['type' => 'google_analytics_id'],
                    ['value' => $request->input('TRACKING_ID') ?? null]
                );
            }
        }

        BusinessSetting::updateOrCreate(
            ['type' => 'google_analytics'],
            ['value' => $request->input('google_analytics') ?? 0]
        );

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }
    public function google_tagmanager_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);

            if ($type == 'TAG_MANAGER_ID') {
                BusinessSetting::updateOrCreate(
                    ['type' => 'google_tagmanager_id'],
                    ['value' => $request->input('TAG_MANAGER_ID') ?? null]
                );
            }
        }

        BusinessSetting::updateOrCreate(
            ['type' => 'google_tagmanager'],
            ['value' => $request->input('google_tagmanager') ?? 0]
        );

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    public function google_recaptcha_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'google_recaptcha')->first();

        if ($request->has('google_recaptcha')) {
            $business_settings->value = 1;
            $business_settings->save();
        }
        else{
            $business_settings->value = 0;
            $business_settings->save();
        }

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    public function google_map_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'google_map')->first();

        if ($request->has('google_map')) {
            $business_settings->value = 1;
            $business_settings->save();
        }
        else{
            $business_settings->value = 0;
            $business_settings->save();
        }

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    public function google_firebase_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'google_firebase')->first();

        if ($request->has('google_firebase')) {
            $business_settings->value = 1;
            $business_settings->save();
        }
        else{
            $business_settings->value = 0;
            $business_settings->save();
        }

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }
    public function onesignal_update(Request $request)
    {
        //dd($request);
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }
        $business_settings = BusinessSetting::where('type', 'onesignal')->first();
        if($business_settings!=null){
            $business_settings = $business_settings;
        }else{
            $business_settings = new BusinessSetting;
            $business_settings->type = 'onesignal';

            $business_settings->save();
        }

        if ($request->has('onesignal')) {
            $business_settings->value = 1;
            $business_settings->save();
        }
        else{
            $business_settings->value = 0;
            $business_settings->save();
        }

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }


    /**
     * Update the API key's for GOOGLE analytics.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function facebook_chat_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
                $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'facebook_chat')->first();

        if ($request->has('facebook_chat')) {
            $business_settings->value = 1;
            $business_settings->save();
        }
        else{
            $business_settings->value = 0;
            $business_settings->save();
        }

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    public function facebook_comment_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'facebook_comment')->first();
        if(!$business_settings) {
            $business_settings = new BusinessSetting;
            $business_settings->type = 'facebook_comment';
        }

        $business_settings->value = 0;
        if ($request->facebook_comment) {
            $business_settings->value = 1;
        }

        $business_settings->save();

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    public function facebook_pixel_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
            BusinessSetting::updateOrCreate(
                ['type' => strtolower($type)],
                ['value' => $request->$type ?? null]
            );
        }

        BusinessSetting::updateOrCreate(
            ['type' => 'facebook_pixel'],
            ['value' => $request->input('facebook_pixel') ?? 0]
        );

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    /**
     * Update the API key's for other methods.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function env_key_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
                $this->overWriteEnvFile($type, $request[$type]);
        }

        flash(("Settings updated successfully"))->success();
        return back();
    }

    /**
     * overWrite the Env File values.
     * @param  String type
     * @param  String value
     * @return \Illuminate\Http\Response
     */
    public function overWriteEnvFile($type, $val)
    {
        if(env('DEMO_MODE') != 'On'){
            $path = base_path('.env');
            if (file_exists($path)) {
                $val = '"'.trim($val).'"';
                if(is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0){
                    file_put_contents($path, str_replace(
                        $type.'="'.env($type).'"', $type.'='.$val, file_get_contents($path)
                    ));
                }
                else{
                    file_put_contents($path, file_get_contents($path)."\r\n".$type.'='.$val);
                }
            }
        }
    }

    public function seller_verification_form(Request $request)
    {
    	return view('backend.sellers.seller_verification_form.index');
    }

    /**
     * Update sell verification form.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function seller_verification_form_update(Request $request)
    {
        $form = array();
        $select_types = ['select', 'multi_select', 'radio'];
        $j = 0;
        for ($i=0; $i < count($request->type); $i++) {
            $item['type'] = $request->type[$i];
            $item['label'] = $request->label[$i];
            if(in_array($request->type[$i], $select_types)){
                $item['options'] = json_encode($request['options_'.$request->option[$j]]);
                $j++;
            }
            array_push($form, $item);
        }
        $business_settings = BusinessSetting::where('type', 'verification_form')->first();
        $business_settings->value = json_encode($form);
        if($business_settings->save()){
            Cache::flush();

            flash(("Verification form updated successfully"))->success();
            return back();
        }
    }

    public function update(Request $request)
    {
        // dd($request->all());
        foreach ($request->types as $key => $type) {
            if($type == 'site_name'){
                $this->overWriteEnvFile('APP_NAME', $request[$type]);
            }
            if($type == 'timezone'){
                $this->overWriteEnvFile('APP_TIMEZONE', $request[$type]);
            }
            if($type == 'video_file_driver'){
                $this->overWriteEnvFile('VIDEO_FILE_DRIVER', $request[$type]);
                BusinessSetting::updateOrCreate(
                    ['type' => 'video_file_driver'],
                    ['value' => $request[$type]]
                );
                BusinessSetting::updateOrCreate(
                    ['type' => 'video_url'],
                    ['value' => $request->video_file_driver == 's3' ? env('AWS_URL') : config('app.url')]
                );
            }
            else {
                $lang = null;
                if(gettype($type) == 'array'){
                    $lang = array_key_first($type);
                    $type = $type[$lang];
                    $business_settings = BusinessSetting::where('type', $type)->where('lang',$lang)->first();
                }else{
                    $business_settings = BusinessSetting::where('type', $type)->first();
                }
                // dd($business_settings);

                if(is_array($request->header_menu_labels)){
                    for ($i = 0; $i < count($request->header_menu_labels); $i++):
                    $menup[$i] = [
                        "id"=>$i,
                        "lebel"=>$request->header_menu_labels[$i],
                        "link"=>$request->header_menu_links[$i]
                        ];
                    endfor;
                }
                if($business_settings!=null){
                    if(gettype($request[$type]) == 'array'){
                        $business_settings->value = json_encode($request[$type]);
                        // dd('type array  '.$business_settings->value);
                    }
                    else {
                        $business_settings->value = $request[$type];
                        // dd('type not array  '.$business_settings->value);
                    }
                    $business_settings->lang = $lang;
                    $business_settings->save();
                }
                else{
                    $business_settings = new BusinessSetting;
                    $business_settings->type = $type;
                    if(gettype($request[$type]) == 'array'){
                        $business_settings->value = json_encode($request[$type]);
                    }
                    else {
                        $business_settings->value = $request[$type];
                    }
                    $business_settings->lang = $lang;
                    $business_settings->save();
                }
            }
        }

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    public function moduleActivator(Request $request)
    {
        $module = $request->module;
        if($request->status == 1){
            Module::enable($module);
            dispatch(function () use ($module) {
                Artisan::call('module:migrate '.$module);
            })->afterResponse();
            // Artisan::call('module:migrate '.$module);
        }
        else{
            Module::disable($module);
        }
        Cache::flush();
        return response()->json(['success'=>true,'message'=>'Module updated successfully']);
    }


    // function to update nestate menu labels start
    public function updatenestate(Request $request)
    {

        $customs_menu_find = BusinessSetting::where('type', 'customs_menu_71')->first();
        if(!$customs_menu_find){
            $customs_menu_find = new BusinessSetting;
            $customs_menu_find->type = 'customs_menu_71';
            $customs_menu_find->value = $request->menu;
            $customs_menu_find->lang = 'en';
        }else{
            $customs_menu_find->value = $request->menu;
        }
        if ($customs_menu_find->save()) {
            Cache::flush();
           return response()->json(['status'=>'success','message'=>'Menu updated successfully']);
        }
    }
    // function to update nestate menu labels end

    public function updateActivationSettings(Request $request)
    {
        $env_changes = ['FORCE_HTTPS', 'FILESYSTEM_DRIVER'];
        if (in_array($request->type, $env_changes)) {

            return $this->updateActivationSettingsInEnv($request);
        }

        $business_settings = BusinessSetting::where('type', $request->type)->first();
        if($business_settings!=null){
            if ($request->type == 'maintenance_mode' && $request->value == '1') {
                if(env('DEMO_MODE') != 'On'){
                    dispatch(function () {
                        Artisan::call('down');
                    })->afterResponse();
                    // Artisan::call('down');
                }
            }
            elseif ($request->type == 'maintenance_mode' && $request->value == '0') {
                if(env('DEMO_MODE') != 'On') {
                    dispatch(function () {
                        Artisan::call('up');
                    })->afterResponse();
                    // Artisan::call('up');
                }
            }
            $business_settings->value = $request->value;
            $business_settings->save();
        }
        else{
            $business_settings = new BusinessSetting;
            $business_settings->type = $request->type;
            $business_settings->value = $request->value;
            $business_settings->save();
        }

        Cache::flush();
        return '1';
    }

    public function updateActivationSettingsInEnv($request)
    {
        if ($request->type == 'FORCE_HTTPS' && $request->value == '1') {
            $this->overWriteEnvFile($request->type, 'On');

            if(strpos(env('APP_URL'), 'http:') !== FALSE) {
                $this->overWriteEnvFile('APP_URL', str_replace("http:", "https:", env('APP_URL')));
            }

        }
        elseif ($request->type == 'FORCE_HTTPS' && $request->value == '0') {
            $this->overWriteEnvFile($request->type, 'Off');
            if(strpos(env('APP_URL'), 'https:') !== FALSE) {
                $this->overWriteEnvFile('APP_URL', str_replace("https:", "http:", env('APP_URL')));
            }

        }
        elseif ($request->type == 'FILESYSTEM_DRIVER' && $request->value == '1') {
            $this->overWriteEnvFile($request->type, 's3');
        }
        elseif ($request->type == 'FILESYSTEM_DRIVER' && $request->value == '0') {
            $this->overWriteEnvFile($request->type, 'local');
        }

        return '1';
    }

    public function vendor_commission(Request $request)
    {
        return view('backend.sellers.seller_commission.index');
    }

    public function vendor_commission_update(Request $request){
        foreach ($request->types as $key => $type) {
            $business_settings = BusinessSetting::where('type', $type)->first();
            if($business_settings!=null){
                $business_settings->value = $request[$type];
                $business_settings->save();
            }
            else{
                $business_settings = new BusinessSetting;
                $business_settings->type = $type;
                $business_settings->value = $request[$type];
                $business_settings->save();
            }
        }

        Cache::flush();

        flash(('Seller Commission updated successfully'))->success();
        return back();
    }

    public function shipping_configuration(Request $request){
        return view('backend.setup_configurations.shipping_configuration.index');
    }

    public function shipping_configuration_update(Request $request){
        $business_settings = BusinessSetting::where('type', $request->type)->first();
        $business_settings->value = $request[$request->type];
        $business_settings->save();

        Cache::flush();
        return back();
    }

    public function doctorsConsultationStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'banner' => 'required',
            'button_text' => 'required|string|max:100',
            'button_link' => 'nullable|url|max:255',
            'show_experience_card' => 'nullable|boolean',
            'card_title' => 'nullable|required_if:show_experience_card,1|string|max:50',
            'card_rating' => 'nullable|required_if:show_experience_card,1|numeric|min:1|max:5',
        ]);

        if ($validator->fails()) {
            flash('Validation failed!')->error();
            Session::put('doctorsConsultationHasErrors', true);
            return back()->withInput()->withErrors($validator);
        }

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'banner' => $request->banner,
            'button_text' => $request->button_text,
            'button_link' => $request->button_link,
            'show_experience_card' => intval($request->show_experience_card),
            'card_title' => $request->card_title,
            'card_rating' => $request->card_rating,
        ];

        BusinessSetting::updateOrCreate(
            ['type' => 'doctors_consultation'],
            ['value' => json_encode($data)]
        );

        Session::forget('doctorsConsultationHasErrors');
        Cache::flush();
        flash('Doctors Consultation section updated successfully')->success();
        return back();
    }
}
