<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class devicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user_type = Auth::user()->type_id;
        $user_id = Auth::user()->id;
        $user_addedby = Auth::user()->added_by;
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

        $records = DB::table('distance')
            ->orderBy('created_at', 'desc')
            ->paginate(20);


        $devices = DB::table('devices')
            ->Join(
                'sites',
                'sites.id',
                '=',
                'devices.site_id'
            )
            ->join('location', 'devices.location_id', '=', 'location.id')
            ->select('devices.*', 'sites.site_name', 'location.location')
            ->orderBy('devices.created_at', 'desc')
            ->paginate(20);



        $our_sites = DB::table('sites')->paginate(20);


        $users = DB::table('users')->orderBy('created_at', 'desc')
            ->paginate(20);



        return response()->json([
            'devices' => $devices,
            'notifications' => $notifications,
            'users' => $users,
            'our_sites' => $our_sites,
            'user_type' => $user_type,
            'user_addedby' => $user_addedby
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     //
    //     return view('devices');
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the form data

        try {
            $validatedData = $request->validate([
                'device_id' => 'required|unique:devices,device_id',
                'device_model' => 'required',
                'site' => 'required',
                'type' => 'required',
                'location' => 'required',
                'status' => 'required'
            ]);

            $device = new Device();
            $device->user_id = Auth::user()->type_id;;
            $device->device_id = $validatedData['device_id'];
            $device->model = $validatedData['device_model'];
            $device->site_id = $validatedData['site'];
            $device->type = $validatedData['type'];
            $device->location_id = $validatedData['location'];
            $device->status = $validatedData['status'];
            $device->save();

            $devices = DB::table('devices')
                ->Join(
                    'sites',
                    'sites.id',
                    '=',
                    'devices.site_id'
                )
                ->join('location', 'devices.location_id', '=', 'location.id')
                ->select('devices.*', 'sites.site_name', 'location.location')
                ->orderBy('devices.created_at', 'desc')
                ->paginate(20);



            $message = 'Successfully inserted';
            $success = true;
            $records = DB::table('distance')->paginate(20);


            // return view('device_management', compact('message', 'success', 'records', 'devices',));
            return response()->json([
                'message' => 'Device created successfully',
            ], Response::HTTP_OK);
                } catch (\Exception $e) {

             // return view('device_management', compact('message', 'records', 'success'));
            return response()->json([
                'message' => 'Failed to add device: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user_type = Auth::user()->type_id;
        $user_addedby = Auth::user()->added_by;
        // Retrieve a specific device
        $device =
            DB::table('devices')
            ->join('sites', 'sites.id', '=', 'devices.site_id')
            ->join('location', 'sites.id', '=', 'location.site_id')
            ->select('devices.*', 'sites.site_name', 'location.location')
            ->where('devices.device_id', $id)
            ->first();

        // dd($device);

        $records = DB::table('distance')
            ->where('device_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Return the view with the device data
        return response()->json([
            'device' => $device,
            'records'=> $records,
            'user_type' => $user_type
        ], Response::HTTP_OK);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     //
    //     $device = Device::find($id);

    //     // Return the view for editing the device
    //     // return view('devices.edit', compact('device'));
    // }

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
        // Validate the form data

        try {
            $validatedData = $request->validate([
                'device_id' => 'required',
                'device_model' => 'required',
                'site' => 'required',
                'type' => 'required',
                'location' => 'required'
            ]);
            // Update the device
            $device = Device::where('device_id', $id)->first();
            $device->user_id = $request->input('user_assigned');
            $device->device_id = $validatedData['device_id'];
            $device->model = $validatedData['device_model'];
            $device->site_id = $validatedData['site'];
            $device->type = $validatedData['type'];
            $device->location_id = $validatedData['location'];
            $device->updated_at = Carbon::now();

            // Save the updated device
            $device->save();

            // return redirect()->route('device_management')->with('success', 'Device updated successfully');

            return response()->json([
                'success' => 'Device updated successfully',
                ], Response::HTTP_OK);
        } catch (\Exception $e) {

            return response()->json([
                'failure ' => 'Device failed to update' . $e->getMessage(),
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
        $device = Device::where('device_id', $id)->first();

        if ($device) {
            $deviceId = $device->device_id;
            DB::table('distance')->where('device_id', $deviceId)->delete();
            $device->delete();
            // Redirect to the devices index page
            return response()->json([
                'success ' => 'Device deleted successfully',
            ], Response::HTTP_OK);
        } else {
            return redirect()->route('device_management')->with('failure', 'Failed to delete');
            return response()->json([
                'failure'=>'Failed to delete',
            ], Response::HTTP_OK);
        }
    }
}
