<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cuisine;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Exports\CuisineExport;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
 


class CuisineController extends Controller
{
/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cuisine = Cuisine::withcount('restaurants')
        ->search(keywords:$request['search'], mainCol: ['name','id'])
        ->latest()
        ->paginate(config('default_pagination'));
        return view('admin-views.cuisine.index',compact('cuisine'));
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:cuisines|max:100',
            'image' => 'nullable|max:2048',
        ], [
            'name.required' => translate('messages.Name is required!'),
        ]);
        if($request->name[array_search('default', $request->lang)] == '' ){
            Toastr::error(translate('default_cuisine_name_is_required'));
            return back();
            }
        $cuisine = new Cuisine();
        $cuisine->name = $request->name[array_search('default', $request->lang)];
        $cuisine->image = $request->has('image') ? Helpers::upload(dir:'cuisine/',format: 'png', image: $request->file('image')) : 'def.png';
        $cuisine->meta_title =  $request->meta_title[array_search('default', $request->lang)];
        $cuisine->meta_description =  $request->meta_description[array_search('default', $request->lang)];
        $cuisine->meta_image = $request->file('meta_image') ? Helpers::upload(dir:'meta_image/', format: 'png',image: $request->file('meta_image')): $cuisine->meta_image;
        $cuisine->meta_data = (Helpers::formatMetaData($request->all(), $cuisine->meta_data));
        $cuisine->save();
        // Helpers::add_or_update_translations(request: $request, key_data:'meta_title' , name_field:'meta_title' , model_name: 'Cuisine' ,data_id: $cuisine->id,data_value: $cuisine->meta_title);
        // Helpers::add_or_update_translations(request: $request, key_data:'meta_description' , name_field:'meta_description' , model_name: 'Cuisine' ,data_id: $cuisine->id,data_value: $cuisine->meta_description);
        $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type'  => 'App\Models\Cuisine',
                            'translationable_id'    => $cuisine->id,
                            'locale'                => $key,
                            'key'                   => 'name'
                        ],
                        ['value'                 => $cuisine->name]
                    );
                }
            } else {
                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type'  => 'App\Models\Cuisine',
                            'translationable_id'    => $cuisine->id,
                            'locale'                => $key,
                            'key'                   => 'name'
                        ],
                        ['value'                 => $request->name[$index]]
                    );
                }
            }
        }
        Toastr::success(translate('messages.Cuisine_added_successfully'));
        return back();
    }

    public function create()
    {
        $language = getWebConfig('language');

        return response()->json([
            'view' => view('admin-views.cuisine.partials._create', compact( 'language'))->render(),
        ]);
    }

    public function edit($id)
    {
        $cuisine = Cuisine::withoutGlobalScope('translate')->findOrFail($id);
        $language = getWebConfig('language');

        return response()->json([
            'view' => view('admin-views.cuisine.partials._edit', compact('cuisine', 'language'))->render(),
        ]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'name.0' => 'required|max:100|unique:cuisines,name,'.$request->id,
            'image' => 'nullable|max:2048',
        ], [
            'name.0.required' => translate('messages.Name is required!'),
        ]);

        if($request->name[array_search('default', $request->lang)] == '' ){
            Toastr::error(translate('default_cuisine_name_is_required'));
            return back();
            }
        $cuisine = Cuisine::find($request->id);
        $cuisine->name = $request->name[array_search('default', $request->lang)];

        $slug = Str::slug($cuisine->name);
        $cuisine->slug = $cuisine->slug? $cuisine->slug :"{$slug}-{$cuisine->id}";

        $cuisine->image = $request->has('image') ? Helpers::update(dir:'cuisine/', old_image:$cuisine->image, format:'png', image:$request->file('image')) : $cuisine->image;
        $cuisine->meta_title =  $request->meta_title[array_search('default', $request->lang)];
        $cuisine->meta_description =  $request->meta_description[array_search('default', $request->lang)];
        if ($request->input('meta_image_remove') == "1") {
            Helpers::check_and_delete('meta_image/',$cuisine->meta_image);
            $cuisine->meta_image = null;
        }
        $cuisine->meta_image = $request->file('meta_image') ? Helpers::upload(dir:'meta_image/', format: 'png',image: $request->file('meta_image')): $cuisine->meta_image;
        $cuisine->meta_data = (Helpers::formatMetaData($request->all(), $cuisine->meta_data));
        $cuisine->save();
        // Helpers::add_or_update_translations(request: $request, key_data:'meta_title' , name_field:'meta_title' , model_name: 'Cuisine' ,data_id: $cuisine->id,data_value: $cuisine->meta_title);
        // Helpers::add_or_update_translations(request: $request, key_data:'meta_description' , name_field:'meta_description' , model_name: 'Cuisine' ,data_id: $cuisine->id,data_value: $cuisine->meta_description);
        $default_lang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type'  => 'App\Models\Cuisine',
                            'translationable_id'    => $cuisine->id,
                            'locale'                => $key,
                            'key'                   => 'name'
                        ],
                        ['value'                 => $cuisine->name]
                    );
                }
            } else {
                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type'  => 'App\Models\Cuisine',
                            'translationable_id'    => $cuisine->id,
                            'locale'                => $key,
                            'key'                   => 'name'
                        ],
                        ['value'                 => $request->name[$index]]
                    );
                }
            }
        }
        Toastr::success(translate('messages.Cuisine_updated_successfully'));
        return back();
    }


    public function status(Request $request)
    {
        $cuisine = Cuisine::find($request->id);
        $cuisine->status = $request->status;
        $cuisine->save();
        Toastr::success(translate('messages.Cuisine_status_updated'));
        return back();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cuisine  $cuisine
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $cuisine = Cuisine::findOrFail($request->id);
        Helpers::check_and_delete('cuisine/' , $cuisine['image']);
        $cuisine?->translations()?->delete();
        $cuisine->delete();
        Toastr::success('cuisine removed!');
        return back();
    }

    public function export(Request $request){
        try{

            $cuisine = Cuisine::withcount('restaurants')->search(keywords:$request['search'], mainCol: ['name','id'])->latest()
            ->get();
            $data=[
                'data' =>$cuisine,
                'search' =>$request['search'] ?? null,
            ];
            if($request->type == 'csv'){
                return Excel::download(new CuisineExport($data), 'Cuisine.csv');
            }
            return Excel::download(new CuisineExport($data), 'Cuisine.xlsx');
        }  catch(\Exception $e)
            {
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
        }

    }
}
