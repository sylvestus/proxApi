<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
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
        $userId = Auth::user()->id;
        $notifications = DB::table('notification')
            ->join(
                'users',
                'notification.user_id',
                '=',
                'users.id'
            )
            ->where('notification.user_id', $userId)
            ->orderBy('notification.created_at', 'desc')
            ->take(4)
            ->select('notification.id as notification_id', 'notification.subject as subject', 'notification.message as message', 'notification.is_read as status', 'notification.created_at as created_at', 'users.id as user_id', 'users.name as user_name', 'users.email as user_email')
            ->get();
        $records = DB::table('users')->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'users' => $records,
            'notifications' => $notifications,
            'user_type' => $user_type,
            'user_addedby' => $user_addedby,
            'userId' => $userId
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
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone_no' => 'required',
            'password' => 'required',
            're-password' => 'required'
        ]);
        try {
            $user = new User();
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->phone_no = $validatedData['phone_no'];
            $user->type_id = $request->input('user_type');
            $user->added_by = Auth::user()->id;
            $password = $validatedData['password'];
            $rePassword = $validatedData['re-password'];

            if ($password === $rePassword) {
                $user->password = bcrypt($password); // filledh the password before saving
            } else {
                // Passwords do not match, handle the error (e.g., display an error message)
                // Redirect back to the form with an error message or take appropriate action
            }
            $user->created_at = Carbon::now();

            // Save the updated user
            $user->save();

            return response()->json(['success' => 'user Added successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['failure' => 'user failed to Add:  ' . $e->getMessage()], Response::HTTP_OK);
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
        $user_type = Auth::user()->type_id;
        $userId = Auth::user()->id;
        //
        $notifications = DB::table('notification')
            ->join(
                'users',
                'notification.user_id',
                '=',
                'users.id'
            )
            ->where('notification.user_id', $userId)
            ->orderBy('notification.created_at', 'desc')
            ->take(4)
            ->select('notification.id as notification_id', 'notification.subject as subject', 'notification.message as message', 'notification.is_read as status', 'notification.created_at as created_at', 'users.id as user_id', 'users.name as user_name', 'users.email as user_email')
            ->get();
        $user = DB::table('users')->orderBy('created_at', 'desc')
            ->join('user_types', 'users.type_id', '=', 'user_types.id')
            ->where('users.id', $id)
            ->select('users.*', 'user_types.type as added')
            ->first();
        // dd($user);

        return response()->json([
            'user' => $user,
            'notifications' => $notifications,
            'user_type' => $user_type,
            'userId' => $userId
        ], Response::HTTP_OK);
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
        try {
            // Update the user
            $user = User::where('id', $id)->first();

            if ($request->file('profile_image')) {
                $profileImage = $request->file('profile_image');
                // dd($profileImage);
                $imageName = time() . '.' . $profileImage->getClientOriginalExtension();

                // Define the storage path for the profile image
                $imagePath = 'uploads/profiles/' . $imageName;

                // Move the uploaded image to the storage location
                $profileImage->move(public_path('uploads/profiles'), $imageName);
                $user->profile_image = $imagePath;
            }
            // dd($user);

            if ($request->filled('name')) {
                $user->name = $request->input('name');
            }
            if ($request->filled('email')) {
                $user->email = $request->input('email');
            }
            if ($request->filled('phone_no')) {
                $user->phone_no = $request->input('phone_no');
            }
            if ($request->filled('password')) {
                $password = $request->input('password');
                $rePassword = $request->input('re-password');

                if ($password === $rePassword) {
                    $user->password = bcrypt($password); // filledh the password before saving
                } else {
                    // Passwords do not match, handle the error (e.g., display an error message)
                    // Redirect back to the form with an error message or take appropriate action
                }
            }

            $user->updated_at = Carbon::now();

            // Save the updated user
            $user->save();

            return response()->json(['success' => 'user updated successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {


            return response()->json(
                ['failure' => 'user failed to update' . $e->getMessage()],
                Response::HTTP_OK
            );
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
    }
}
