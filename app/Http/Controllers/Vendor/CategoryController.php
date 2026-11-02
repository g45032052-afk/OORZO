<?php

namespace App\Http\Controllers\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    function index(Request $request)
    {
        $key = explode(' ', $request['search']) ?? null;
        $taxData = Helpers::getTaxSystemType(false);
        $categoryWiseTax = $taxData['categoryWiseTax'];

        $categories=Category::with('childes')->where(['position'=>0])->latest()

        ->when(isset($key) , function($query) use($key){
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        })
         ->with($categoryWiseTax ? ['taxVats.tax'] : [])
        ->paginate(config('default_pagination'));
        return view('vendor-views.category.index',compact('categories','categoryWiseTax'));
    }

    public function get_all(Request $request){
        $data = Category::where('name', 'like', '%'.$request->q.'%')->limit(8)->get([DB::raw('id, CONCAT(name, " (", if(position = 0, "'.translate('messages.main').'", "'.translate('messages.sub').'"),")") as text')]);
        if(isset($request->all))
        {
            $data[]=(object)['id'=>'all', 'text'=>'All'];
        }
        return response()->json($data);
    }

    function sub_index(Request $request)
    {
        $key = explode(' ', $request['search']) ?? null;
        $categories=Category::with(['parent'])->where(['position'=>1])
        ->when(isset($key) , function($query) use($key){
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        })
        ->latest()->paginate(config('default_pagination'));
        return view('vendor-views.category.sub-index',compact('categories'));
    }

}
