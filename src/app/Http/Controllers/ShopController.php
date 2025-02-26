<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ReseController;

use App\Http\Requests\ShopRequest;

use App\Models\Favorite;
use App\Models\Reserve;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;

use Auth;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function shopAll() {
        $auths = Auth::user();
        $shops = Shop::all();
        $shopAreas = DB::select('SELECT DISTINCT area FROM shops');
        $shopGenres = DB::select('SELECT DISTINCT genre FROM shops');
        
        $favorites = Favorite::all();
        //$averageRatings = $this->reviewStar();
        $averageRatings = ReseController::reviewStar();
        
        return view('shop_all', compact('shops', 'shopAreas', 'shopGenres', 'favorites', 'auths', 'averageRatings'));
    }
    
    public function shopDetail(Request $request) {
        $requests = $request->all();
        $dt = Carbon::now();
        $auths = Auth::user();
        $auth = $auths->id;
        
        $user = User::all();
        
        $reviews = Review::where('shop_id', $request->id)->get();
        
        return view('shop_detail', compact('requests', 'dt', 'auth', 'user', 'reviews'));
    }
    
    public function shopCreate(Request $request) {
        $requests = $request->all();
        
        $dir = 'image';
        $file_name = $request->file('image')->getClientOriginalName();
        $request->file('image')->storeAs('public/' . $dir, $file_name);
        
        $params = [
            'user_id' => $request['user_id'],
            'name' => $requests['name'],
            'area' => $requests['area'],
            'genre' => $requests['genre'],
            'description' => $requests['description'],
            'image_path' => 'storage/' . $dir . '/' . $file_name,
        ];
        
        Shop::create($params);
        
        return view('thanks_shop_create');
    }
    
    public function shopManager() {
        $auth = Auth::user();
        $shop = DB::table('shops')
        ->where('user_id', $auth->id)
        ->first();
        
        return view('shop_manager', compact('auth', 'shop'));
    }
    
    public function shopReserve() {
        $auth = Auth::user();
        $users = User::all();
        
        $shop = DB::table('shops')
        ->where('user_id', $auth->id)
        ->first();
        
        $reserves = DB::table('reserves')
        ->where('shop_id', $shop->id)
        ->get();
        
        $currentDate = Carbon::now()->toDateString();
        
        $pastReserves = [];
        $todayReserves = [];
        $futureReserves = [];
        
        foreach ($reserves as $reserve) {
            $reserveDate = Carbon::parse($reserve->date);
            
            if ($reserveDate->lt($currentDate)) {
                $pastReserves[] = $reserve;
            } elseif ($reserveDate->eq($currentDate)) {
                $todayReserves[] = $reserve;
            } else {
                $futureReserves[] = $reserve;
            }
        }
        
        return view('shop_reserve', compact('users', 'shop', 'pastReserves', 'todayReserves', 'futureReserves'));
    }
    
    public function shopUpdate(ShopRequest $request) {
        $requests = $request->all();
        
        $dir = 'image';
        $file_name = $request->file('image')->getClientOriginalName();
        $request->file('image')->storeAs('public/' . $dir, $file_name);
        
        $shops = DB::table('shops')
        ->where('id', $requests['shop_id'])
        ->first();
        
        $params = [
            'name' => $requests['name'],
            'area' => $requests['area'],
            'genre' => $requests['genre'],
            'description' => $requests['description'],
            'image_path' => 'storage/' . $dir . '/' . $file_name,
        ];
        $nonNull = array_filter($params, function ($value) {
            return $value !== null;
        });
        
        Shop::where('id', $requests['shop_id'])->update($nonNull);
        
        //$this->imageUpload($params, $requests);
        
        return view('thanks_shop_create');
    }
}
