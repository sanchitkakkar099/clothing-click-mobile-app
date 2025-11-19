<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\posts;
use App\style;
use App\brand;
use App\User;
use App\Http\Requests\Admin\AddAdminConditionRequest;
use App\Http\Requests\Admin\UpdateAdminConditionRequest;
use Config;
use Carbon\Carbon;
use App\location;
use DB;


class ManagePostsController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Posts']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
		$locations  = location::select('city')
            ->distinct('city')->get();
		$styles  = style::all();
		$brands  = brand::all();
		$categories =\DB::select(\DB::raw(  "SELECT id, name, parent_id FROM categories ORDER BY CASE WHEN parent_id = 0 THEN id  ELSE parent_id  END, parent_id, id;"));
		//echo "<pre>";print_r($categories);echo "</pre>";exit;
		$savedSearches = [];
		$city_array =[];
        $users= posts::where(function($q)use($filter) {
					if(!empty($filter['location'])){
						$city_array =[];
							$get_all_locations = location::select('id')->where([['city',$filter['location']]])->get();
							$searchResult ="select id from location where city = '".$filter['location']."'";
							$savedSearches = \DB::select(\DB::raw($searchResult));
							$savedSearches = json_decode(json_encode($savedSearches), true);
							foreach($savedSearches as $s){
								$city_array[] =$s['id'];
							}
							$q->whereIn('locations', $city_array);
					}
					if(!empty($filter['gender_id'])){
						$q->where('gender_id',$filter['gender_id']);
					}
					if(!empty($filter['brand'])){
						$q->where('brand_id',$filter['brand']);
					}
					if(!empty($filter['style'])){
						$q->where('style_id',$filter['style']);
					}
					if(!empty($filter['category'])){
						$q->where('categories','LIKE',$filter['category']);
						$q->orwhere('categories','LIKE',$filter['category'].',%');
						$q->orwhere('categories','LIKE','%,'.$filter['category'].',%');
						$q->orwhere('categories','LIKE','%,'.$filter['category']);
                    }
					if(!empty($filter['title'])){
						$q->where('title','LIKE','%'.$filter['title'].'%');
                    }
					if(!empty($filter['is_sold']) ){
						$q->where('is_sold',$filter['is_sold']);
                    }
				})
			->orderBy('id', 'desc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_posts.list', compact('breadcum', 'users', 'filter','locations','styles','brands','categories'));
    }

//    public function create() {
//        $breadcum = $this->breadcum;
//         $categorydata = category::getList();
//        return view('admin.manage_condition.create', compact('breadcum','categorydata'));
//    }
//
//    public function store(AddAdminConditionRequest $request) {
//        $all = $request->all();
//        $all['is_active'] = 1;
//        $role = condition::create($all);
//        return redirect()->route('adminCondition')->with('success', 'New admin condition added successfully.');
//    }
//
//    public function edit($id) {
//        $user = condition::find($id);
//        $breadcum = $this->breadcum;
//         $categorydata = category::getList();
//        return view('admin.manage_condition.update', compact('breadcum', 'user','categorydata'));
//    }
//
//    public function update(UpdateAdminConditionRequest $request) {
//        $all = $request->all();
//        condition::find($all['uid'])->update($all);
//        return redirect()->route('adminCondition')->with('success', 'Condition updated successfully.');
//    }

//    public function publish(Request $request) {
//        try {
//            $record = User::find($request->id);
//            if (!empty($record)) {
//                if ($record->is_active == 1) {
//                    User::find($record->id)->update(['is_active' => 0]);
//                } else {
//                    User::find($record->id)->update(['is_active' => 1]);
//                }
//                return 'success';
//            }
//            return 'Record not found';
//        } catch (\Exception $e) {
//            return $e->getMessage();
//        }
//    }

    public function delete($id) {
//        if($id == 1 || $id == 2){
//            return redirect()->back()->with('error','You can not delete admin location city');
//        }
        posts::destroy($id);
        return redirect()->back()->with('success', 'App Post deleted successfully.');
    }
	public function sold_analytics(Request $request){
		$filter = $request->all();
        $breadcum = "Sold Analytics";
		$posts = posts::where(['is_sold'=>1])
                        ->orderBy('id', 'asc')->count();
		$total_posts = $posts;
		$this_month = posts::where(['is_sold'=>1])->whereMonth('sold_at', Carbon::now())->count();
		$this_year = posts::where(['is_sold'=>1])->whereYear('sold_at', date('Y'))->count();
		$today = posts::where(['is_sold'=>1])->whereDate('created_at', Carbon::today())->count();
		$locations  = location::select('city')
            ->distinct('city')->get();
		$savedSearches = [];
		$city_array =[];
        $users=  posts::where(function($q)use($filter) {
					if(!empty($filter['location']) && $filter['search'] == ''){
						$city_array =[];
							$get_all_locations = location::select('id')->where([['city',$filter['location']]])->get();
							$searchResult ="select id from location where city = '".$filter['location']."'";
							$savedSearches = \DB::select(\DB::raw($searchResult));
							$savedSearches = json_decode(json_encode($savedSearches), true);
							foreach($savedSearches as $s){
								$city_array[] =$s['id'];
							}
							$q->whereIn('locations', $city_array);
					}elseif (!empty($filter)) {
						$q->where('title', 'LIKE', '%' . $filter['search'] . '%');
						$q->orWhere('search_brand', 'LIKE', '%' . $filter['search'] . '%');
						$q->orWhere('search_category', 'LIKE', '%' . $filter['search'] . '%');
						$q->orWhere('search_color', 'LIKE', '%' . $filter['search'] . '%');
						$q->orWhere('search_condition', 'LIKE', '%' . $filter['search'] . '%');
						$q->orWhere('search_size', 'LIKE', '%' . $filter['search'] . '%');
						$q->orWhere('search_gender', 'LIKE', '%' . $filter['search'] . '%');
						$style= style::select('id')->where('name', 'LIKE', '%' . $filter['search'] . '%')->first();
						if($style){
							$q->orWhere('style_id', $style->id);
						}						
						$username= User::select('id')->where('username', 'LIKE', '%' . $filter['search'] . '%')->first();
						if($style){
							$q->orWhere('user_id', $username->id);
						}
						if(!empty($filter['location']) && $filter['location'] != ' '){
						$city_array =[];
							$get_all_locations = location::select('id')->where([['city',$filter['location']]])->get();
							$searchResult ="select id from location where city = '".$filter['location']."'";
							
							$savedSearches = \DB::select(\DB::raw($searchResult));
							
							$savedSearches = json_decode(json_encode($savedSearches), true);
							foreach($savedSearches as $s){
								$city_array[] =$s['id'];
							}
							$q->WhereIn('locations', $city_array);
						}
					}else{}
				})
				->where(['is_sold'=>1])
				->orderBy('id', 'desc')->paginate(Config::get('constants.pageRecords'));
		
		
		return view('admin.manage_sold_posts.list', compact('breadcum', 'total_posts','this_year','this_month','today', 'filter','users','locations')); 
	}
	public function top_sellers(Request $request){
		$filter = $request->all();
        $breadcum = "Sold Analytics";
		$posts = posts::where(function($q)use($filter) {
					if(!empty($filter['location'])) {
						$get_all_locations = location::select('id')->where([['city',$filter['location']]])->get();
						$searchResult ="select id from location where city = '".$filter['location']."'";
						$savedSearches = \DB::select(\DB::raw($searchResult));
						$savedSearches = json_decode(json_encode($savedSearches), true);
						foreach($savedSearches as $s){
							$city_array[] =$s['id'];
						}
						$q->whereIn('locations', $city_array);
					}else{}
				})
				->where(['is_sold'=>1])
                        ->groupBy('user_id')->get();
		$details  = [];
		$locations  = location::select('city')
            ->distinct('city')->get();
		foreach($posts as $p){
			$detail = [];
			$detail['user_details'] = $p->user->username;
			$posts = posts::where(['is_sold'=>1,'user_id'=>$p->user_id])
                        ->orderBy('id', 'asc')->count();
			$detail['total_sold'] = $posts;
			$this_month = posts::where(['is_sold'=>1,'user_id'=>$p->user_id])->whereMonth('sold_at', Carbon::now())->count();
			$detail['this_month'] = $this_month;
			$this_year = posts::where(['is_sold'=>1,'user_id'=>$p->user_id])->whereYear('sold_at', date('Y'))->count();
			$detail['this_year'] = $this_year;
			$today = posts::where(['is_sold'=>1])->whereDate('created_at', Carbon::today())->count();
			$location = Location::where(['id' => $p->locations])->first();
			$detail['today'] = $today;
			$detail['location'] = $location->city;
			
			$details[]=$detail;
		}
		return view('admin.manage_total_seller.list', compact('breadcum', 'details', 'filter','locations')); 
	}

}
