<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Subscribed;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MySubscriptionContoller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user_id = Auth::user()->id;
        $subscriptionIds = DB::table('subscribed_packages')
            ->select('subscription_package_id', 'status', 'isPayed', 'next_payment', 'no_of_devices', 'subscription.*')
            ->where('user_id', $user_id)
            ->join('subscription', 'subscribed_packages.subscription_package_id', '=', 'subscription.id')
            ->orderBy('status', 'asc')
            ->paginate(20);

        $packagesSubed = DB::table('subscribed_packages')
            ->select('subscription_package_id', 'status', 'isPayed', 'no_of_devices', 'subscription.*', 'receipts.total_amount as rotal_receipt_amount', 'receipts.id as receipt_id')
            ->where('user_id', $user_id)
            ->join('subscription', 'subscribed_packages.subscription_package_id', '=', 'subscription.id')
            ->join('receipts', 'subscribed_packages.receipt_id', '=', 'receipts.id')
            ->orderBy('status', 'asc')
            ->paginate(20);

        // dd($packagesSubed);
        return response()->json([
            'mysubscriptions' => $subscriptionIds,
            'receiptedSubscriptions' => $packagesSubed
        ], Response::HTTP_OK);
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


    public function confirm_payment_reciept(Request $request, $id)
    {
        // dd($request);
        try {

            $subscriptionPackageId = $request->input('rows')['subscription_package_id'];
            $subscriptionPackageIdDuration = $request->input('rows')['sub_duration'];


            $subscriptionPackage = Subscribed::where('subscription_package_id', $subscriptionPackageId)
                ->where('receipt_id', $id)
                ->first();

            if ($subscriptionPackage) {
                $subscriptionPackage->isPayed = "Yes";
                if ($subscriptionPackageIdDuration == "monthly") {
                    $createdDate = $subscriptionPackage->created_at;
                    $newDate = date('Y-m-d H:i:s', strtotime($createdDate . ' +1 month'));
                    $subscriptionPackage->next_payment = $newDate;
                }
                if ($subscriptionPackageIdDuration == "yearly") {
                    $createdDate = $subscriptionPackage->created_at;
                    $newDate = date('Y-m-d H:i:s', strtotime($createdDate . ' +1 year'));
                    $subscriptionPackage->next_payment = $newDate;
                }

                $subscriptionPackage->save();
            }
            // $subscribedPackage->isPayed = "Yes";
            // $subscribedPackage->save();

            // Return a response if necessary
            $request->session()->flash('success', 'Subscription payment confirmed ');

            return response()->json([
                'success' => true,
                'message' => 'Subscription payment confirmed'
            ]);
        } catch (\Exception $e) {

            $request->session()->flash('failure', 'Subscription payment confirmation failed');
            return response()->json([
                'error' => true,
                'message' => 'Subscription payment confirmation failed' . $e->getMessage()
            ]);
        }
    }

    public function save_reciept(Request $request)
    {
        // dd($request);
        try {
            $user_id = Auth::user()->id;

            $checkedRows = $request->input('rows');
            $total = $request->input('total');
            $receipt = new Receipt();
            $receipt->total_amount = $total;
            $receipt->save();
            $receiptId = $receipt->id;

            // Iterate over the checkedRows array
            foreach ($checkedRows as $rowData) {
                $subscriptionPackageId = $rowData['subscription_package_id'];
                $subscribedPackage = Subscribed::where('subscription_package_id', intval($subscriptionPackageId))
                    ->where('user_id', intval($user_id))
                    ->first();
                if ($subscribedPackage->isPayed != "Yes") {
                    $subscribedPackage->receipt_id = $receiptId;
                    $subscribedPackage->save();
                }
            }
            return response()->json([
                'success' => 'subscription reciept generated successfully',
            ]);
            // Return a response if necessary
        } catch (\Exception $e) {

            return response()->json([
                'failure' => 'subscription reciept failed to generate' . $e->getMessage(),
            ], Response::HTTP_OK);
        }
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
        // dd($request->sub_status);
        //
        $user_id = Auth::user()->id;


        try {
            $substatus = Subscribed::where('subscription_package_id', intval($id))
                ->where('user_id', intval($user_id))
                ->first();

            $substatus->status = $request->sub_status;

            $substatus->save();

            return response()->json(['success' => 'your subscription status has been updated  successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['failure'=> 'failed to update subscription' . $e->getMessage()], Response::HTTP_OK);
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
        try {

            $subId = $sub->id;
            DB::table('subscribed_packages')->where('subscription_package_id', $subId)->delete();

            // Redirect to the subs index page
            return response()->json(['success'=>'subscription deleted successfully'], Response::HTTP_OK);

        } catch (\Exception $e) {

            return response()->json([
                'failure' => 'Failed to delete' . $e->getMessage(),
            ], Response::HTTP_OK);
        }
    }
}
