<?php

namespace App\Http\Controllers;


use App\Models\Address;
use App\Models\AddressUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressUserController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $user_id)
    {

        $request->validate([
            'address_type_id' => 'required',
            'address_line_1' => 'required',
            'town' => 'required',
            'region' => 'required',
            'postal_code' => 'required',
        ]);

        $address = new Address([
            'address_line_1' => $request->get('address_line_1'),
            'address_line_2' => $request->get('address_line_2'),
            'town' => $request->get('town'),
            'region' => $request->get('region'),
            'post_code' => $request->get('postal_code'),
            'country_id' => $request->get('country_id'),
        ]);
        $address->save();

        DB::table('address_user')
            ->updateOrInsert(
                ['user_id' => $user_id, 'address_id' => $address->id],
                ['address_type_id' => $request->get('address_type_id'),
                    'preferred_contact_address' => $request->get('preferred_contact_address')]
            );

        if($request->get('preferred_contact_address')==true){
            DB::table('address_user')
                ->where('user_id', '=', $user_id)
                ->where('address_id', '!=', $address->id)
                ->update(['preferred_contact_address'=>false]);

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
    public function update(Request $request, $user_id, $address_id)
    {
        $request->validate([
            'address_type_id' => 'required',
            'address_line_1' => 'required',
            'town' => 'required',
            'region' => 'required',
            'postal_code' => 'required',
        ]);

        $address = Address::where('id', '=', $address_id)->first();
        $address->address_line_1 = $request->get('address_line_1');
        $address->address_line_2 = $request->get('address_line_2');
        $address->town = $request->get('town');
        $address->region = $request->get('region');
        $address->post_code = $request->get('postal_code');
        $address->country_id = $request->get('country_id');
        $address->save();

        DB::table('address_user')
            ->updateOrInsert(
                ['user_id' => $user_id, 'address_id' => $address->id],
                ['address_type_id' => $request->get('address_type_id'),
                    'preferred_contact_address' => $request->get('preferred_contact_address')],
            );

        if($request->get('preferred_contact_address')==true){
            DB::table('address_user')
                ->where('user_id', '=', $user_id)
                ->where('address_id', '!=', $address_id)
                ->update(['preferred_contact_address'=>false]);

        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($user_id, $address_id)
    {
        $user = User::where('id', $user_id)->first();
        $user->addresses()->detach($address_id);

        $other_count = AddressUser::where('address_id', '=', $address_id)->count();
        if (!$other_count) {
            $address = Address::where('id', '=', $address_id)->first();
            $address->delete();
        }
    }

    /**
     * Extract, for a given user, the address types that they do not already have an address
     * associated with - the implication being that a user can only have on address
     * for each available address type
     *
     * @param $user_id
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableAddressTypes($user_id)
    {
        $available_addresses = DB::table('address_types')
            ->select('address_type', 'id')
            ->whereNotIn('id', DB::table('address_user')
                ->select('address_type_id')
                ->where('user_id', '=', $user_id)->pluck('address_type_id'))
            ->orderBy('address_type')
            ->pluck('address_type', 'id');

        return $available_addresses;
    }
}
