<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\PhoneUser;
use App\Models\User;
use App\Models\UserTypeStatus;
use App\Models\UserUserType;
use App\Searches\UserSearch\UserSearch;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use mysql_xdevapi\Warning;
use function Psy\debug;

class UserController extends Controller
{
     /**
     * Retrieve the Users in the system as a paginated lsit filetered
     * by the criteria managed in the UserSearch function
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $user_list = UserSearch::apply($request);
        //$user_list=User::with('roles')
        //->with('permissions')
        //    ->with('userUserType')
        //    ->with('userUserType.userType')
        //->with('userUserType.userTypeStatus');


        if ($request->has('recordsPerPage')) {
            $recordsPerPage = $request->recordsPerPage;
        } else {
            $recordsPerPage = 5;
        }
        return $user_list->paginate($recordsPerPage);
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
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function show($id)
    {
        /*
         * ->with('roles')->with('permissions')->first()
         */
        $user=User::where('id',$id)
            ->with('roles')
            ->with('permissions')
            ->with('userUserType.userType')
            ->with('userUserType.userTypeStatus')
            ->first();
        return $user;
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
        $user = User::findOrFail($id);
        /*
         * AGP validate first and last names rather than just name
         */
        $validated = $request->validateWithBag('updateProfileInformation', [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        /*
         * AGP use first and last name concatenation
         */
        if ($request['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $request);
        } else {
            $user->forceFill([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'name' => $request['first_name'] . ' ' . $request['last_name'],
                'email' => $request['email'],
            ])->save();
        }
    }

    protected function updateVerifiedUser($user, Request $request)
    {
        $user->forceFill([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'name' => $request['first_name'] . ' ' . $request['last_name'],
            'email' => $request['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
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

        $other_count = PhoneUser::where('address_id', ' = ', $address_id)->count();
        if (!$other_count) {
            $address = Address::where('id', ' = ', $address_id)->first();
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
        $user = User::where('id', $user_id)->first();
        if (!is_null($user)) {
            $user_addresses = DB::table('address_user')
                ->join('users', 'address_user.user_id', '=', 'users.id')
                ->join('addresses', 'address_user.address_id', '=', 'addresses.id')
                ->join('countries', 'addresses.country_id', '=', 'countries.id')
                ->join('address_types', 'address_user.address_type_id', '=', 'address_types.id')
                ->select('users.id', 'address_types.*', 'addresses.*', 'countries.country', 'address_user.preferred_contact_address')
                ->where('users.id', '=', $user_id)
                ->get();

            return $user_addresses;
        } else {
            return null;
        }
    }

    /**
     * Retrieve the Phone numbers and types for this User
     *
     * @param $user_id
     * @return \Illuminate\Support\Collection|null
     */
    public function getUserPhones($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if (!is_null($user)) {
            $user_phones = DB::table('phone_user')
                ->join('users', 'phone_user.user_id', '=', 'users.id')
                ->join('phones', 'phone_user.phone_id', '=', 'phones.id')
                ->join('countries', 'phones.country_id', '=', 'countries.id')
                ->join('phone_types', 'phone_user.phone_type_id', '=', 'phone_types.id')
                ->select('users.id', 'phone_types.*', 'phones.*', 'countries.country', 'phone_user.preferred_contact_number')
                ->where('users.id', '=', $user_id)
                ->get();

            return $user_phones;
        } else {
            return null;
        }
        // return Address::where('user_id',$user_id)->get();
    }

    /**
     * Retrieve the Spatie roles assigned to this User
     *
     * @param $user_id
     * @return mixed
     */
    public function getUserRoles($user_id)
    {
        $user = User::where('id', $user_id)->first();
        return $user->getRoleNames();
    }

    /**
     * Retrieve the Spatie permissions assigned to this User
     *
     * @param $user_id
     * @return mixed
     */
    public function getUserPermissions($user_id)
    {
        $user = User::where('id', $user_id)->first();
        return $user->getPermissionNames();
    }

    /**
     * Extract the User Types assigned to this User
     *
     * @param $user_id
     * @return \Illuminate\Support\Collection|null
     */
    public function getUserUserTypes($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if (!is_null($user)) {
            $user_user_types = DB::table('user_user_type')
                ->join('users', 'user_user_type.user_id', '=', 'users.id')
                ->join('user_types', 'user_user_type.user_type_id', '=', 'user_types.id')
                ->join('user_type_statuses', 'user_user_type.user_type_status_id', '=', 'user_type_statuses.id')
                ->select('user_user_type.user_type_id', 'user_types.*', 'user_type_statuses.*')
                ->where('users.id', '=', $user_id)
                ->get();

            return $user_user_types;
        } else {
            return null;
        }
    }

    /**
     * Update the User Types associated with a user.
     * The request contains an array of User Types that will include all User Types available
     * in the system. Each User Type will have a status of either
     * Active
     * Inactive
     * Undefined
     *
     * Undefined denotes that this particular User Type has never been associated with the User
     * Active and Inactive may be the existing status of the User Type or a change from the previous
     * status - eg Active has now been made Inactive. We only want to update real changes to status so we filter
     * Undefined and any User Type whose status has not changed from updates to the system.
     *
     * @param $user_id
     * @param Request $request
     * @return void
     */
    public function updateUserTypes($user_id,Request $request){
        $user = User::where('id', $user_id)->first();           /* find the user */
        $user_types=$request->all();                            /* extract the User Types from the request */
        /*
         * Enumerate the User Types
         *      Ignore those with status of Undefined
         *      For User Types with statuses other than Undefined...
         *          Check if we already have this User Type attached to this User
         *          If we don't
         *              Create a new UserUserType record with the relevant status
         *          If we do, see whether the status has changed
         *              If so
         *                  Update the status
         *              Otherwise
         *                  Ignore it as that status hasn't been changed
         *
         */
        foreach ($user_types as $user_type){
            /* Ignore the Undefined ones as they are not assocated with this User*/
            if($user_type['user_type_status']!=='Undefined'){
                /* get the user_type_status record corresponding to the one passed in the request */
                $user_type_status_current=UserTypeStatus::where('user_type_status','=',$user_type['user_type_status'])->first();

                /* see if we can locate a record for the user for this particular user _type */
                $user_user_type=UserUserType::where('user_id','=',$user_id)
                    ->where('user_type_id','=',$user_type['id'])
                    ->first();

                /* if we didn't find a record...*/
                if($user_user_type===null){
                    /* add the new UserType to the user_user_type pivot table */
                    $user->userTypes()->attach([
                       $user_type['id']=>['user_type_status_id'=>$user_type_status_current->id]
                    ]);
                    /* if we did find a record and the user_type_status has changed then update that record */
                }else if($user_user_type->user_type_status_id !=$user_type_status_current->id){
                    $user_user_type->update(['user_type_status_id'=>$user_type_status_current->id]);
                }
            }
        }
    }
}
