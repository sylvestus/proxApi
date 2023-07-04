<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\NotificationConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    //
    public function index(){
        $user_id = Auth::user()->id;
        $avatar =Auth::user()->profile_image;
        // dd($user_id);
        $notifications = DB::table('notification')
        ->join(
            'users',
            'notification.user_id',
            '=',
            'users.id'
        )
            ->where('notification.user_id', $user_id)
            ->orderBy('notification.created_at', 'desc')
            ->take(4)
            ->select('notification.id as notification_id', 'notification.subject as subject', 'notification.message as message', 'notification.is_read as status', 'notification.created_at as created_at', 'users.id as user_id', 'users.name as user_name', 'users.email as user_email')
            ->get();
        $config = NotificationConfig::where('user_id', $user_id)->first();
            // dd($config);
        return response()->json([
            'user_id'=> $user_id,
            'avatar'=> $avatar,
            'notifications'=> $notifications,
            'notificationConfigs'=> $config], Response::HTTP_OK);

    }
}
