<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    function start_time()
    {
        $firstUser = User::orderBy('created_at', 'asc')->first();
        return response()->json([
            'success' => true,
            'message' => '取得に成功しました',
            'data' => [
                'start_time' => $firstUser
            ]
        ]);
    }

    public function enter(Request $request)
    {
        $request->validate([
            'sns_id' => 'required|string',
        ]);

        DB::table('sns_id')->insert([
            'sns_id' => $request->sns_id,
        ]);

        return response()->json(['message' => 'saved']);
    }

    function user_all()
    {
        return User::select('id', 'sns_id')->get()->map(function ($user) {

            // SNS ID がある場合は API を叩く
            try {
                $response = Http::get(config('services.dealer.api_url') . "/api/account/show/{$user->sns_id}");

                if ($response->successful()) {
                    $snsUser = $response->json('data.user', []);

                    return [
                        'id' => $user->id,
                        'name' => $snsUser['name'] ?? "ゲスト{$user->id}",
                        'user_icon' => $snsUser['user_icon'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('SNS API エラー', ['user_id' => $user->id]);
            }

            // SNS なし or API エラー
            return [
                'id' => $user->id,
                'name' => "ゲスト{$user->id}",
                'user_icon' => null,
            ];
        });
    }

    function roulette_result(Request $request)//ルーレットの結果が入ってくる。
    {
        try {
        $result = $request->input('roulette_result');//ルーレットの結果(例: 7,5など)

        $user_all = User::all();
        $p = $user_all->map(function ($user){

        });
        } catch (\Exception $e){
            Log::error('SNS API エラー', ['user_id' => $user->id]);
        }
    }

    function stake(Request $request)
    { //賭け金、どこに賭けたか、user_id
        try {
            $stake = $request->input('stake');
            $bet_place = $request->input('bet_place');//場所
            $user_id = $request->input('id');

            $user = User::find($user_id);
            $user->update([
                'stake' => $stake,
                'bet_place' => $bet_place
            ]);

            return response()->json([
            'success' => true,
            'message' => '取得に成功しました'
        ]);
        } catch (\Exception $e) {
            return response()->json([
            'success' => false,
            'message' => '取得に失敗しました'
        ]);
        }
    }


    // function user_all()
    // {
    //     $user = User::select('sns_id')->get()->toArray();
    //     if ($user->sns_id) {
    //         try {
    //             $response = Http::get(config('services.dealer.api_url') . "/api/account/show/{$user->sns_id}");

    //             if ($response->successful()) {
    //                 $snsUser = $response->json('data.user', []);
    //                 return [
    //                     'name' => $snsUser['name'] ?? "ゲスト{$user->id}",
    //                     'user_icon' => $snsUser['user_icon'] ?? null
    //                 ];
    //             }

    //             Log::warning('SNS API レスポンスエラー', [
    //                 'status' => $response->status(),
    //                 'body' => $response->body()
    //             ]);
    //         } catch (\Exception $e) {
    //             Log::error('SNS API エラー: ' . $e->getMessage(), [
    //                 'sns_id' => $user->sns_id,
    //                 'exception' => get_class($e)
    //             ]);
    //         }
    //     }

    //     return [
    //         'name' => "ゲスト{$user->id}",
    //         'user_icon' => null
    //     ];
    // }


    // function index(){
    //     $active_player = Active_player::where('is_guest', false)->get();
    //     $roulette_result = Roulette_result::all(); //場所

    //     foreach ($active_player as $index){
    //         $user = $index['user_id'];
    //         $stake = $index['stake']; //賭けたお金
    //         $betting_place = $index['betting_place']; //賭けた場所


    //     }
    // }

    //     function index(Request $request){ //$requestにはsns_idが入っている。
    //     $user_id = $request->input('user_id');
    //     $user_name = $request->input('user_name');
    //     $icon = $request->input('icon');
    //     $stake = $request->input('stake');
    //     $is_guest = $request->input('is_guest');
    //     $betting_place = $request->input('betting_place');
    //     //ここは「実行できたら」を確認しているわけだから大丈夫
    //     try{
    //         Active_player::create([
    //             'user_id' => $user_id,
    //             'user_name' => $user_name,
    //             'icon' => $icon,
    //             'stake' => $stake,
    //             'is_guest' => $is_guest
    //         ]);

    //         return response()->json([
    //         'success' => true,
    //         'message' => '取得に成功しました'
    //     ]);
    //     } catch (\Exception $e){
    //         report($e);
    //         session()->flash('flash_message', '更新が失敗しました');//あってもなくてもいい？

    //         return response()->json([
    //         'success' => false,
    //         'message' => '取得に失敗しました'
    //         ]);
    //     }
    // }

    //     function index(Request $request){
    //     //これはIFでおけ、try/catchは「実行できなかったら」をチェックするものだから
    //     $result = $request->input('roulette_result'); //場所だけが入る。
    //     // $resultがある場合、Roulette_resultテーブルの、resultカラムに保存。
    //     if($result){
    //         Roulette_result::create([
    //             'result' => $result,
    //         ]);
    //         return response()->json([
    //         'success' => true,
    //         'message' => '取得に成功しました',
    //         'data' => [
    //             'roulette' => $result
    //         ]
    //     ]);
    //     }
    //     return response()->json([
    //         'message' => 'バリデーションエラーが発生しました。',
    //         'errors' => [
    //             'field_name' => ['このフィールドは必須です。'],
    //         ]
    //     ], 422);
    // }
}
