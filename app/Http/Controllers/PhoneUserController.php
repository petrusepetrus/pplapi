<?php

namespace App\Http\Controllers;

use App\Models\Phone;
use App\Models\PhoneUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhoneUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * Store a newly created resource
     *
     * @param Request $request
     * @param $userID
     */
    public function store(Request $request, $userID)
    {
        $request->validate([
            'phone_type_id' => 'required',
            'phone_number' => 'required',
            'country_id' => 'required'
        ]);

        $phone = new Phone([
            'phone_number' => $request->get('phone_number'),
            'country_id' => $request->get('country_id'),
        ]);
        $phone->save();

        DB::table('phone_user')
            ->updateOrInsert(
                ['user_id' => $userID, 'phone_id' => $phone->id],
                ['phone_type_id' => $request->get('phone_type_id'),
                    'preferred_contact_number' => $request->get('preferred_contact_number')]
            );

        if ($request->get('preferred_contact_number') == true) {
            DB::table('phone_user')
                ->where('user_id', '=', $userID)
                ->where('phone_id', '!=', $phone->ID)
                ->update(['preferred_contact_number' =>  $request->get('preferred_contact_number')]);

        }

        if ($request->get('preferred_contact_number') == true) {
            DB::table('phone_user')
                ->where('user_id', '=', $userID)
                ->where('phone_id', '!=', $phone->id)
                ->update(['preferred_contact_number' => false]);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user_id, $phone_id)
    {
        $request->validate([
            'phone_type_id' => 'required',
            'phone_number' => 'required',
            'country_id' => 'required'
        ]);

        $phone = Phone::where('id', '=', $phone_id)->first();
        $phone->phone_number = $request->get('phone_number');
        $phone->country_id = $request->get('country_id');
        $phone->save();

        DB::table('phone_user')
            ->updateOrInsert(
                ['user_id' => $user_id, 'phone_id' => $phone->id],
                ['phone_type_id' => $request->get('phone_type_id'),
                    'preferred_contact_number' => $request->get('preferred_contact_number')],
            );

        if ($request->get('preferred_contact_number') == true) {
            DB::table('phone_user')
                ->where('user_id', '=', $user_id)
                ->where('phone_id', '!=', $phone->id)
                ->update(['preferred_contact_number' => false]);

        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $user_id
     * @param $phone_id
     */
    public function destroy($user_id, $phone_id)
    {
        $user = User::where('id', $user_id)->first();
        $user->phones()->detach($phone_id);

        $other_count = PhoneUser::where('phone_id', '=', $phone_id)->count();
        if (!$other_count) {
            $address = Phone::where('id', '=', $phone_id)->first();
            $address->delete();
        }
    }

    /**
     * Extract, for a given user, the phone number types that they do not already have a phone number
     * associated with - the implication being that a user can only have one phone number
     * for each available phone number type
     *
     * @param $user_id
     * @return \Illuminate\Support\Collection
     */
    public function getAvailablePhoneTypes($user_id)
    {
        $available_phones = DB::table('phone_types')
            ->select('phone_type', 'id')
            ->whereNotIn('id', DB::table('phone_user')
                ->select('phone_type_id')
                ->where('user_id', '=', $user_id)->pluck('phone_type_id'))
            ->orderBy('phone_type')
            ->pluck('phone_type', 'id');

        return $available_phones;
    }
}
