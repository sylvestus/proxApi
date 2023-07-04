<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Subscribed;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user_type = Auth::user()->type_id;
        $user_addedby = Auth::user()->added_by;
        $subscriptions = DB::table('subscription')->orderBy('sub_amount', 'desc')->paginate(20);

        $devices = DB::table('devices')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return response()->json([
            'subscriptions' => $subscriptions,
            'devices' => $devices,
            'user_type' => $user_type], Response::HTTP_OK);
    }

    public function subscribe(Request $request, $id)
    {
        // $sub = Subscribed::where('sub_id', $id)->first();

        try {
            $userId = Auth::user()->id;
            $sub_id = $id;
            $subscribe = new Subscribed();
            $subscribe->user_id = $userId;


            // Check if the user is already subscribed to the package
            $existingSubscription = Subscribed::where('user_id', $userId)
                ->where('subscription_package_id', $sub_id)
                ->first();

            if ($existingSubscription) {
                // The user is already subscribed to the package
                return response()->json([
                    'failure'=>'You are already subscribed to this package could be in your subscription history'
                ], Response::HTTP_OK);
            }

            $subscribe->subscription_package_id = $sub_id;
            $subscribe->no_of_devices = $request->input('subsc_devices');
            $subscribe->status = "Active";
            $subscribe->isPayed = "No";
            $subscribe->created_at = Carbon::now();
            $subscribe->save();
            return response()->json([
                'success'=>'package subscribed successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {

            // return view('subscriptions', compact('message', 'success', 'subscriptions','devices'));
            return response()->json([
                'failure' => 'subscription failer to create ' . $e->getMessage()], Response::HTTP_OK);
        }
    }

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
        //
        // Validate the form data
        $validatedData = $request->validate([
            'sub_name' => 'required',
            'sub_duration' => 'required',
            'sub_device_type' => 'required',
            'sub_amount' => 'required',
        ]);

        try {
            $subscription = new Subscription();
            $subscription->sub_name = $validatedData['sub_name'];
            $subscription->sub_duration = $validatedData['sub_duration'];
            $subscription->sub_device_type = $validatedData['sub_device_type'];
            $subscription->sub_amount = $validatedData['sub_amount'];
            $subscription->save();

            // return view('subscriptions', compact('message', 'success', 'subscriptions', 'devices'));
            return response()->json([
                'success' => 'subscription created successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {

            // return view('subscriptions', compact('message', 'success', 'subscriptions','devices'));
            return response()->json(['failure' => 'subscription failer to create ' . $e->getMessage()], Response::HTTP_OK);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        //
        try {
            // Update the user
            $sub = Subscription::where('id', $id)->first();

            if ($request->filled('sub_name')) {
                $sub->sub_name = $request->input('sub_name');
            }
            if ($request->filled('sub_duration')) {
                $sub->sub_duration = $request->input('sub_duration');
            }
            if ($request->filled('sub_device_type')) {
                $sub->sub_name = $request->input('sub_name');
            }
            if ($request->filled('sub_amount')) {
                $sub->sub_amount = $request->input('sub_amount');
            }

            $sub->updated_at = Carbon::now();

            // Save the updated user
            $sub->save();

            return
                response()->json([
                    'success'=>'subscription updated successfully'
                ], Response::HTTP_OK);
        } catch (\Exception $e) {


            return response()->json([
                'failure' => 'subscription failed to update' . $e->getMessage()
            ], Response::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $sub = Subscription::find($id);
        if ($sub) {
            $subId = $sub->id;
            DB::table('subscription')->where('id', $subId)->delete();
            $sub->delete();
            // Redirect to the subs index page
            return response()->json([
                'success' => 'subscription deleted successfully'
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'failure' => 'Failed to delete'
            ], Response::HTTP_OK);
        }
    }
}
