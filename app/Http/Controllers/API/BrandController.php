<?php

namespace App\Http\Controllers\API;

use App\brand;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Validator;

class BrandController extends APIResponseController
{

    public function search(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:55',
            ]);

            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            if(strlen($request->name) > 1){
				$cordinates = [
					'latitude'  => $all['latitude'],
					'longitude' => $all['longitude']
				];

				$gender_id = isset($all['gender_id']) ? $all['gender_id'] : null;
							$user = Auth::user();
				if ($user) {

					// STEP 1: Filter brands using whereHas (just like the second code)
					$brandIds = brand::select('id')
						->whereHas('posts', function ($query) use ($user, $gender_id) {

							$query->PrivateAccountPost($user)
								->where('is_active', 1)
								->where('is_delete', 0)
								->where('is_sold', 0);

							if (in_array((int) $gender_id, [1,2], true)) {
								$query->where('gender_id', (int) $gender_id);
							}
						})
						->where('name', 'LIKE', '%' . $all['name'] . '%')
						->orderBy('id','desc')
						->pluck('id')
						->toArray();

					// STEP 2: Load posts same as second code
					$posts = brand::with([
						'posts' => function ($query) use ($user, $cordinates, $gender_id) {

							$query->whereHas('user', function ($q) {
								$q->where('is_active', 1);
							})
							->PrivateAccountPost($user)
							->GetNearestPostDashboard($cordinates, true)
							->where('posts.is_active', 1)
							->where('posts.is_delete', 0)
							->where('posts.is_sold', 0);

							if (in_array((int) $gender_id, [1,2], true)) {
								$query->where('posts.gender_id', (int) $gender_id);
							}

							$query->distinct('posts.id');
						}
					])
					->whereIn('id', $brandIds);

				} else {

					// GUEST MODE: Apply same logic as second code
					$brandIds = brand::select('id')
						->whereHas('posts', function ($query) use ($gender_id) {
							$query->where('is_active', 1)
								->where('is_delete', 0)
								->where('is_sold', 0);

							if (in_array((int) $gender_id, [1,2], true)) {
								$query->where('gender_id', (int) $gender_id);
							}
						})
						->where('name', 'LIKE', '%' . $all['name'] . '%')

						->orderBy('id','desc')
						->pluck('id')
						->toArray();

					$posts = brand::with([
						'posts' => function ($query) use ($cordinates, $gender_id) {

							$query->whereHas('user', function ($q) {
								$q->where('is_active', 1);
							})
							->GetNearestPostDashboard($cordinates, true)
							->where('posts.is_active', 1)
							->where('posts.is_delete', 0)
							->where('posts.is_sold', 0);

							if (in_array((int) $gender_id, [1,2], true)) {
								$query->where('posts.gender_id', (int) $gender_id);
							}

							$query->distinct('posts.id');
						}
					])
					->whereIn('id', $brandIds);
				}

				// Sorting (same behavior)
				if (isset($all['sort_by']) && !empty($all['sort_by'])) {

					switch ($all['sort_by']) {

						case 'date':
							$posts->orderBy('id', $all['sort_value']);
							break;

						case 'distance':
							$posts->orderBy('posts_count', $all['sort_value']);
							break;

						default:
							// do nothing
							break;
					}

				} else {
					$posts->orderBy('id','desc');
				}

				$posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
				// Convert posts to same format as IF block
$finalBrands = $posts->getCollection()->map(function($brand){
    return [
        'id'         => $brand->id,
        'brand_id'   => $brand->id,
        'name'       => $brand->name,
        'image'      => $brand->image,
        'photo'      => $brand->photo,
        'posts_count'=> $brand->posts->count(),
    ];
});

// Sort by posts_count DESC
$finalBrands = $finalBrands->sortByDesc('posts_count')->values();
$finalBrands = $finalBrands->filter(function($brand) {
    return $brand['posts_count'] > 0;
})->values();
                
			return $this->SendResponse($finalBrands, 'Success', 200);            

            //** end

           // return $this->SendResponse($brands, 'Success', 200);
            }else{
				$cordinates = [
					'latitude'  => $all['latitude'],
					'longitude' => $all['longitude']
				];

				$gender_id = isset($all['gender_id']) ? $all['gender_id'] : null;
							$user = Auth::user();
				if ($user) {

					// STEP 1: Filter brands using whereHas (just like the second code)
					$brandIds = brand::select('id')
						->whereHas('posts', function ($query) use ($user, $gender_id) {

							$query->PrivateAccountPost($user)
								->where('is_active', 1)
								->where('is_delete', 0)
								->where('is_sold', 0);

							if (in_array((int) $gender_id, [1,2], true)) {
								$query->where('gender_id', (int) $gender_id);
							}
						})
						->orderBy('id','desc')
						->pluck('id')
						->toArray();

					// STEP 2: Load posts same as second code
					$posts = brand::with([
						'posts' => function ($query) use ($user, $cordinates, $gender_id) {

							$query->whereHas('user', function ($q) {
								$q->where('is_active', 1);
							})
							->PrivateAccountPost($user)
							->GetNearestPostDashboard($cordinates, true)
							->where('posts.is_active', 1)
							->where('posts.is_delete', 0)
							->where('posts.is_sold', 0);

							if (in_array((int) $gender_id, [1,2], true)) {
								$query->where('posts.gender_id', (int) $gender_id);
							}

							$query->distinct('posts.id');
						}
					])
					->whereIn('id', $brandIds);

				} else {

					// GUEST MODE: Apply same logic as second code
					$brandIds = brand::select('id')
						->whereHas('posts', function ($query) use ($gender_id) {
							$query->where('is_active', 1)
								->where('is_delete', 0)
								->where('is_sold', 0);

							if (in_array((int) $gender_id, [1,2], true)) {
								$query->where('gender_id', (int) $gender_id);
							}
						})
						->orderBy('id','desc')
						->pluck('id')
						->toArray();

					$posts = brand::with([
						'posts' => function ($query) use ($cordinates, $gender_id) {

							$query->whereHas('user', function ($q) {
								$q->where('is_active', 1);
							})
							->GetNearestPostDashboard($cordinates, true)
							->where('posts.is_active', 1)
							->where('posts.is_delete', 0)
							->where('posts.is_sold', 0);

							if (in_array((int) $gender_id, [1,2], true)) {
								$query->where('posts.gender_id', (int) $gender_id);
							}

							$query->distinct('posts.id');
						}
					])
					->whereIn('id', $brandIds);
				}

				// Sorting (same behavior)
				if (isset($all['sort_by']) && !empty($all['sort_by'])) {

					switch ($all['sort_by']) {

						case 'date':
							$posts->orderBy('id', $all['sort_value']);
							break;

						case 'distance':
							$posts->orderBy('posts_count', $all['sort_value']);
							break;

						default:
							// do nothing
							break;
					}

				} else {
					$posts->orderBy('id','desc');
				}

				$posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
				// Convert posts to same format as IF block
$finalBrands = $posts->getCollection()->map(function($brand){
    return [
        'id'         => $brand->id,
        'brand_id'   => $brand->id,
        'name'       => $brand->name,
        'image'      => $brand->image,
        'photo'      => $brand->photo,
        'posts_count'=> $brand->posts->count(),
    ];
});

// Sort by posts_count DESC
$finalBrands = $finalBrands->sortByDesc('posts_count')->values();
$finalBrands = $finalBrands->filter(function($brand) {
    return $brand['posts_count'] > 0;
})->values();
// Return ONLY the list (no pagination meta)
return $this->SendResponse($finalBrands, 'Success', 200);            
            }
        } catch (\Exception $e) {
            return $this->SendResponse([], $e->getMessage(), 500);
        }
    }

    public function saveBrand(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:55|unique:brands',
            ],
           [
            'name.unique'=> 'This brand/designer is already added', // custom message
           ]);

            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            $all              = $request->all();
            $all['is_active'] = 1;
            $all['user_id'] = Auth::user()->id;
            $img              = null;
            if ($request->hasFile('photo')) {
                $all['photo'] = UploadImage($request->file('photo'), '', '', '');
            }
            // dd($all);
            $brand = brand::create($all);
            $brand->brand_id = $brand->id;
            return $this->SendResponse($brand, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse([], $e->getMessage(), 500);
        }
    }
    public function list(Request $request)
    {
        try {
            
            //$brand = brand::all();
			$brand =brand::withCount(['posts' => function ($q) {
        $q->where([
            ['posts.is_active', 1],
            ['posts.is_delete', 0],
            ['is_sold', 0]
        ]);
    }])
    ->orderBy('posts_count', 'desc') // 👈 sort by post count
    ->get();
            //$brand->brand_id = $brand->id;
            return $this->SendResponse($brand, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse([], $e->getMessage(), 500);
        }
    }
}
