<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Subscribed;
use App\Models\SaveDistance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MyPNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notification;

class SaveDistanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     */
    public function bootTimechecks()
    {
        $user_id = Auth::user()->id;
        $packegeExpiryWarning = DB::table('subscribed_packages')
            ->select('subscription_package_id', 'status', 'isPayed', 'next_payment', 'no_of_devices', 'subscription.*')
            ->where('user_id', $user_id)
            ->join('subscription', 'subscribed_packages.subscription_package_id', '=', 'subscription.id')
            ->orderBy('status', 'asc')
            ->paginate(20);
        $today = Carbon::today();
        $fiveDaysAhead = $today->copy()->addDays(5);

        foreach ($packegeExpiryWarning as $packegeExpiryWarning) {
            $paymentdate =  Carbon::parse($packegeExpiryWarning->next_payment);
            $packegeName = $packegeExpiryWarning->sub_name;
            $packageDuration = $packegeExpiryWarning->sub_duration;
            $packageSubDeviceType = $packegeExpiryWarning->sub_device_type;
            $packageDeviceNo = $packegeExpiryWarning->no_of_devices;

            if ($paymentdate->isBetween($paymentdate, $fiveDaysAhead, true)) {

                try {
                    $notified_today = MyPNotification::where('user_id', intval($user_id))
                    ->whereDate('created_at', Carbon::today())
                    ->first();
                    if (!$notified_today) {
                        $notificationUpdate = new MyPNotification();
                        $notificationUpdate->user_id = $user_id;
                        $notificationUpdate->subject = "Subscription Early warnig";
                        $notificationUpdate->message = "your subscription " . $packegeName . "  for duration " . $packageDuration . " and " . $packageDeviceNo . " of devices " . $packageSubDeviceType . " is due for subscription in 5 or less days";
                        $notificationUpdate->is_read = "0";
                        $notificationUpdate->save();
                    }

                } catch (\Exception $e) {
                    return response()->json(
                        ['failure'=>'failed failed to notify user of expured  subscriptions' . $e->getMessage()],
                        Response::HTTP_OK
                    );

                }
            }
            if ($paymentdate->isPast()) {

            try {
                    $id = $packegeExpiryWarning->subscription_package_id;
                    $substatus = Subscribed::where('subscription_package_id', intval($id))
                        ->where('user_id', intval($user_id))
                        ->first();

                    $substatus->status = "Inactive";
                    $substatus->isPayed = "No";
                    $substatus->save();


                    $notified_today = MyPNotification::where('user_id', intval($user_id))
                    ->whereDate('created_at', Carbon::today())
                    ->first();
                    if (!$notified_today) {
                        $notificationUpdate = new MyPNotification();
                        $notificationUpdate->user_id = $user_id;
                        $notificationUpdate->subject = "Subscription Update warnig";
                        $notificationUpdate->message = "your subscription " . $packegeName . "  for duration " . $packageDuration . " and " . $packageDeviceNo . " of devices " . $packageSubDeviceType . " is due for subscription ";
                        $notificationUpdate->is_read = "0";
                        $notificationUpdate->save();
                    }

                    return response()->json([
                        'message' => "updated",
                    ], Response::HTTP_OK);

            } catch (\Exception $e) {
                    return response()->json([
                        'failure'=>'failed failed to update expired  subscriptions' . $e->getMessage()], Response::HTTP_OK);

            }
            }
        }
    }

    public function index()
    {
        $this->bootTimechecks();

        $user_id = Auth::user()->id;
        $records = DB::table('distance')->get();

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



        $users = DB::table('users')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $devices = DB::table('devices')
            ->join(
                'sites',
                'sites.id',
                '=',
                'devices.site_id'
            )
            ->select('*')
            ->orderBy('devices.created_at', 'desc')
            ->paginate(20);

        $subscriptions = DB::table('subscription')->paginate(20);

        return response()->json([
            'records' => $records,
            'users' => $users,
            'devices' => $devices,
            'subscriptions' => $subscriptions,
            'notifications' => $notifications], Response::HTTP_OK);

    }



    // device_management

    // public function device_management()
    // {
    //     $records = DB::table('distance')->paginate(20);
    //     return view('device_management',['records'=> $records]);
    // }

    //site and blogs

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $devices =DB::table('devices')->get();
        $device_id = "0";
        foreach($devices as $device){
            $device_id= $device->device_id;
        }
        if
        ($device_id == $request->device_id) {
            try {
                $distance = new SaveDistance();
                $distance->device_id = $request->device_id;
                $distance->name = $request->name;
                $distance->status = $request->status;
                $distance->last_refresh = $request->last_refresh;
                $distance->signal_stregth = $request->signal_strength;
                $distance->tank_level = $request->tank_level;
                $distance->battery_level = $request->battery_level;
                $distance->save();

                return response()->json([
                    'success' => true,
                    'message' => 'successfully inserted'
                ]);
            } catch (\Exception $e) {
                // Log the error message or do something else to handle the error
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to insert distance'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'There is no such device please regiter it from the dashboard'
            ]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaveDistance  $saveDistance
     * @return \Illuminate\Http\Response
     */
    public function show(SaveDistance $saveDistance)
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaveDistance  $saveDistance
     * @return \Illuminate\Http\Response
     */
    public function edit(SaveDistance $saveDistance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SaveDistance  $saveDistance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SaveDistance $saveDistance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaveDistance  $saveDistance
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaveDistance $saveDistance)
    {
        //
    }
}
