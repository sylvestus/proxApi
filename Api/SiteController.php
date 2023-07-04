<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
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
        $our_sites = DB::table('sites')->paginate(20);

        return response()->json(['our_sites' => $our_sites,
        'user_type'=>$user_type], Response::HTTP_OK);

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
        try {
            $site = new Site();
            $site->site_name = $request->input('name');
            $site->save();

            return response()->json(['success'=>'site Added successfully'], Response::HTTP_OK);


        } catch (\Exception $e) {
            // Log the error message or do something else to handle the error
            return response()->json(['failure'=>'failed to Add site'.$e->getMessage()], Response::HTTP_OK);
            }
    }

    public function addSiteLocations(Request $request)
    {
        //
        try {
            $site = new SiteLocation();
            $mysiteID=$request->input('site_id');
            $site->location = $request->input('name');
            $site->site_id=$request->input('site_id');
            $site->save();

            return response()->json(['success'=>'site location Added successfully'], Response::HTTP_OK);

        } catch (\Exception $e) {
            // Log the error message or do something else to handle the error
            return response()->json(['failure'=>'failed to Add site location' . $e->getMessage()], Response::HTTP_OK);

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
        $user_addedby = Auth::user()->added_by;
        //
        $our_sites = DB::table('sites')

            ->join(
                'location',
                'sites.id',
                '=',
                'location.site_id'
            )
            ->where('sites.id', $id)
            ->orderBy('sites.site_name')
            ->select('*', 'location.id as location_id ')
            ->paginate(20);

        return response()->json([
            'our_sites' => $our_sites,
            'current_id'=> $id,
            'user_type'=> $user_type], Response::HTTP_OK);

    }

    public function locationsInASite($id)
    {
        //
        $locations_on_Site = DB::table('location')

            ->where('site_id', $id)
            ->select('*')
            ->get();

        return response()->json(["locations_on_site"=>$locations_on_Site]);
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
            $site = Site::where('id', $id)->first();
            $site->site_name = $request->input('name');
            $site->save();

            return response()->json(['success'=>'site updated successfully'], Response::HTTP_OK);

        } catch (\Exception $e) {
            // Log the error message or do something else to handle the error
            return response()->json([
                'failure'=>'failed to update site'
            ], Response::HTTP_OK);

        }
    }

    public function siteDetailsUpdate(Request $request, $id)
    {
        //
        try {
            $location = SiteLocation::where('id', $id)->first();
            $location->location = $request->input('name');
            $location->save();

            return response()->json(['success'=>'location updated successfully'], Response::HTTP_OK);

        } catch (\Exception $e) {
            // Log the error message or do something else to handle the error
            return response()->json(['failure'=> 'failed to update location' . $e->getMessage()],Response::HTTP_OK);

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
        $site = Site::find($id);
        if ($site) {
            // delete locations registered to this site

            DB::table('location')->where('site_id', $id)->delete();
            // delete the site
            $site->delete();
            // Redirect to the devices index page
            return response()->json(['success'=>'site deleted successfully'], Response::HTTP_OK);

        } else {
            return response()->json(['failure'=>'Failed to delete'], Response::HTTP_OK);

        }
    }


    public function delSiteLocations($id)
    {
        //

        // $site = Site::find($id);
        try {
            // delete locations registered to this site

            DB::table('location')->where('id', $id)->delete();
            // delete the site
            // Redirect to the devices index page
            return response()->json(['success'=> 'site location deleted successfully'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['failure'=> 'Failed to delete' . $e->getMessage()], Response::HTTP_OK);

        }
    }
}
