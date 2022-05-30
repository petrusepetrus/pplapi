<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\PhoneUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->has('recordsPerPage')){
            $recordsPerPage=$request->recordsPerPage;
        }else{
            $recordsPerPage=5;
        }
        $preferredPhoneNumbers=DB::table('phone_user')
            ->join('phones','phone_user.phone_id','=','phones.id')
            ->where('phone_user.preferred_contact_number','=','1')
            ->select('phone_user.preferred_contact_number','phone_user.user_id','phones.phone_number');


        $users=DB::table('users')
            ->leftJoinSub($preferredPhoneNumbers,'preferred_numbers',function($join){
                $join->on('users.id','=','preferred_numbers.user_id');
            })
            ->select('users.*','preferred_numbers.phone_number')
            ->paginate($recordsPerPage);
        return $users;

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
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($user_id,$address_id)
    {
        $user=User::where('id',$user_id)->first();
        $user->addresses()->detach($address_id);

        $other_count=PhoneUser::where('address_id','=',$address_id)->count();
        if(!$other_count){
            $address=Address::where('id','=',$address_id)->first();
            $address->delete();
        }
    }

    /**
     * Get all addresses associated with user
     *
     * @param $user_id
     * @return \Illuminate\Support\Collection
     */
    public function getUserAddresses($user_id)
    {
        $user=User::where('id',$user_id)->first();
        if(!is_null($user)){
            $user_addresses = DB::table('address_user')
                ->join('users','address_user.user_id','=','users.id')
                ->join('addresses','address_user.address_id','=','addresses.id')
                ->join('countries','addresses.country_id','=','countries.id')
                ->join('address_types','address_user.address_type_id','=','address_types.id')
                ->select('users.id','address_types.*','addresses.*','countries.country','address_user.preferred_contact_address')
                ->where('users.id','=',$user_id)
            ->get();

            return $user_addresses;
        }else{
            return null;
        }
        // return Address::where('user_id',$user_id)->get();
    }

    public function getUserPhones($user_id)
    {
        $user=User::where('id',$user_id)->first();
        if(!is_null($user)){
            $user_phones = DB::table('phone_user')
                ->join('users','phone_user.user_id','=','users.id')
                ->join('phones','phone_user.phone_id','=','phones.id')
                ->join('countries','phones.country_id','=','countries.id')
                ->join('phone_types','phone_user.phone_type_id','=','phone_types.id')
                ->select('users.id','phone_types.*','phones.*','countries.country','phone_user.preferred_contact_number')
                ->where('users.id','=',$user_id)
                ->get();

            return $user_phones;
        }else{
            return null;
        }
        // return Address::where('user_id',$user_id)->get();
    }
    public function getUserRoles($user_id){
        $user=User::where('id',$user_id)->first();
        return $user->getRoleNames();
    }
}
