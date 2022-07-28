<?php

namespace App\Searches\UserSearch;

use App\Models\User;
use App\Models\UserType;
use App\Models\UserTypeStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserSearch
{
    public static function apply(Request $filters)
    {
        /*
         * Create the base query of the user with their phones, user types and user type statuses
         */
        $userList = (new User)->newQuery();
        $userList->with('roles')
            ->with('permissions')
            ->with('userUserType')
            ->with('phones')
            ->with('userUserType.userType')
            ->with('userUserType.userTypeStatus');

        //    ->with('userTypes.userTypeStatus');

        /*
         * If we have a request to filter by name apply that filter
         */
        if ($filters->nameQuery !== null) {
            $userList->where('users.name', 'LIKE', '%' . $filters->nameQuery . '%');
        }

        /*
         * The userType and userTypeStatuses can be queried separately or together eg -
         *  We might want all users with 'active' and/or 'inactive' roles, no matter what role it is
         *  We might want all users with specific role(s) whether it is 'active' or 'inactive'
         *  We might want all user with specific roles whether active or inactive
         *
         */
        /*
* The userType and userTypeStatuses can be queried separately or together eg -
*  We might want all users with 'active' and/or 'inactive' roles, no matter what role it is
*  We might want all users with specific role(s) whether it is 'active' or 'inactive'
*  We might want all user with specific roles whether active or inactive
*
*/
        $flgUserTypes = false;
        $flgUserTypeStatuses = false;
        if ($filters->userTypeQuery !== null) {
            $userTypes = explode(',', $filters->userTypeQuery);
            $flgUserTypes = true;
        }
        if ($filters->userTypeStatusQuery !== null) {
            $userTypeStatuses = explode(',', ($filters->userTypeStatusQuery));
            $flgUserTypeStatuses = true;
        }

        /*
         * Do we have a userTypeQuery with a userTypeStqatus query?
         */
        if ($flgUserTypes && $flgUserTypeStatuses) {
            /*
             * If we have both a userType query and a userTypeStatus query then
             * we will filter the dataset at the userUserType level selecting by the qualifying
             * userTypeId's and UserTypeStatusId's
             *
             * First, take the array of User Types passed in the request and
             * create a corresponding array of user_type_ids to filter the query by.
             */
            for ($i = 0; $i < count($userTypes); $i++) {
                $userTypeReturned = UserType::where('user_type', '=', $userTypes[$i])->first();
                $newUserTypes[$i]['id'] = $userTypeReturned->id;
                $newUserTypes[$i]['desc'] = $userTypes[$i];
            }
            /*
             * Repeat for the UserTypeStatuses
             */
            for ($i = 0; $i < count($userTypeStatuses); $i++) {
                Log::warning("iteration " . $i);
                $userTypeStatusReturned = UserTypeStatus::where('user_type_status', '=', $userTypeStatuses[$i])->first();
                Log::warning($userTypeStatusReturned);
                $newUserTypeStatuses[$i]['id'] = $userTypeStatusReturned->id;
                $newUserTypeStatuses[$i]['desc'] = $userTypeStatuses[$i];
            }
            /*
             * Constrain the user records in the first instance to only those that have
             * UserTypes in effect
             */
            $userList->whereHas('userUserType', function ($q) use ($newUserTypes, $newUserTypeStatuses) {
                $firstQueryProcessed = false;
                /*
                 * Iterate each UserType
                 */
                for ($i = 0; $i < count($newUserTypes); $i++) {
                    /*
                     * And the UserTypeStatuses
                     */
                    for ($j = 0; $j < count($newUserTypeStatuses); $j++) {
                        /*
                         * For the first item in the query pairings we want to create an 'and' condition
                         * in the query stack
                         */
                        if (!$firstQueryProcessed) {
                            $firstQueryProcessed = true;
                            $q->where(function ($q2) use ($i, $j, $newUserTypes, $newUserTypeStatuses) {
                                $q2->where('user_user_type.user_type_id', '=', $newUserTypes[$i]['id']);
                                $q2->where('user_user_type.user_type_status_id', '=', $newUserTypeStatuses[$j]['id']);
                            });
                            /*
                             * But for subsequent query pairings we require an 'or'
                             */
                        } else {
                            $q->orWhere(function ($q2) use ($i, $j, $newUserTypes, $newUserTypeStatuses) {
                                $q2->where('user_user_type.user_type_id', '=', $newUserTypes[$i]['id']);
                                $q2->where('user_user_type.user_type_status_id', '=', $newUserTypeStatuses[$j]['id']);
                            });
                        }
                    }
                }
            });
        } else
            if ($flgUserTypes) {
                /* otherwise, we just have a userType filter and no statuses to worry about */
                $userList->whereHas('userUserType.userType', function ($q) use ($userList, $userTypes, $filters) {
                    $blnFirstUserTypeFound = false;
                    $queryArray = array();
                    for ($i = 0; $i < count($userTypes); $i++) {
                        $queryArray [$i] = [
                            ['user_types.user_type', '=', $userTypes[$i]],
                        ];
                        if (!$blnFirstUserTypeFound) {
                            $blnFirstUserTypeFound = true;
                            $q->where([$queryArray]);
                        } else {
                            $q->orWhere([$queryArray]);
                        }
                        $queryArray = array();
                    }
                });
            } else
                if ($flgUserTypeStatuses) {
                    /* otherwise, we just have a userTypeStatus filter and no userTypes to worry about */
                    if ($filters->userTypeStatusQuery !== null) {
                        $userList->whereHas('userUserType.userTypeStatus', function ($q) use ($userList, $userTypeStatuses, $filters) {
                            $blnFirstUserTypeFound = false;
                            $queryArray = array();
                            if (count($userTypeStatuses) > 0) {
                                for ($j = 0; $j < count($userTypeStatuses); $j++) {
                                    $queryArray [$j] = [
                                        ['user_type_statuses.user_type_status', '=', $userTypeStatuses[$j]]
                                    ];
                                    if (!$blnFirstUserTypeFound) {
                                        $blnFirstUserTypeFound = true;
                                        $q->where([$queryArray]);
                                    } else {
                                        $q->orWhere([$queryArray]);
                                    }
                                    $queryArray = array();

                                }
                            }
                        });
                    }
                }
        return $userList;
    }

    public
    function holdingPlace($filters)
    {
        $userList = null;
        /*
    * The userType and userTypeStatuses can be queried separately or together eg -
    *  We might want all users with 'active' and/or 'inactive' roles, no matter what role it is
    *  We might want all users with specific role(s) whether it is 'active' or 'inactive'
    *  We might want all user with specific roles whether active or inactive
    *
    */
        $flgUserTypes = false;
        $flgUserTypeStatuses = false;
        if ($filters->userTypeQuery !== null) {
            $userTypes = explode(',', $filters->userTypeQuery);
            $flgUserTypes = true;
        }
        if ($filters->userTypeStatusQuery !== null) {
            $userTypeStatuses = explode(',', ($filters->userTypeStatusQuery));
            $flgUserTypeStatuses = true;
        }

        /*
         * Do we have a userTypeQuery with a userTypeStqatus query?
         */
        if ($flgUserTypes && $flgUserTypeStatuses) {
            /*
            If we have both a userType query and a userTypeStatus query ...
            create the models basis to apply a query to, userTypes with embedded UserTypeStatus
            and create a call back to attach the process the filter through*/
            $userList->where(function ($subquery) use ($userList, $userTypes, $userTypeStatuses, $filters) {
                $subquery->whereHas('userUserType.userTypeStatus', function ($q1) use ($userList, $userTypes, $userTypeStatuses, $filters) {
                    $q1->whereHas('userUserType.userType', function ($q2) use ($q1, $userTypes, $userTypeStatuses, $filters) {
                        $blnFirstUserTypeFound = false;
                        $queryArray = array();
                        /* for each userType */
                        for ($i = 0; $i < count($userTypes); $i++) {
                            /* if we have any userTypeStatuses */
                            if (!$blnFirstUserTypeFound) {
                                $blnFirstUserTypeFound = true;
                                if (count($userTypeStatuses) > 0) {
                                    /* for each UserTypeStatus
                                    /* construct a 'type = type and status = status' query */
                                    for ($j = 0; $j < count($userTypeStatuses); $j++) {
                                        $queryArray [$j] = [
                                            ['user_types.user_type', '=', $userTypes[$i]],
                                            ['user_type_statuses.user_type_status', '=', $userTypeStatuses[$j]]
                                        ];
                                        /* if this is the first one, wrap the condition in a 'where' statement
                                            otherwise we need and 'orWhere' structure
                                        */
                                        if ($j == 0) {
                                            $q2->where([$queryArray]);
                                        } else {
                                            $q2->orWhere([$queryArray]);
                                        }
                                        $queryArray = array();
                                    }
                                }
                                /* otherwise, if we are on a subsequent userType then we just need 'orWhere' queries */
                            } else {
                                $queryArray = array();
                                if (count($userTypeStatuses) > 0) {
                                    for ($j = 0; $j < count($userTypeStatuses); $j++) {
                                        $queryArray[$j] = [
                                            ['user_types.user_type', '=', $userTypes[$i]],
                                            ['user_type_statuses.user_type_status', '=', $userTypeStatuses[$j]]
                                        ];
                                        $q2->orWhere([$queryArray]);
                                        $queryArray = array();
                                    }
                                }
                            }
                        }
                        //return $q2;
                    });
                });
            });
        } else if ($flgUserTypes) {
            /* otherwise, we just have a userType filter and no statuses to worry about */
            $userList->whereHas('userUserType.userType', function ($q) use ($userList, $userTypes, $filters) {
                $blnFirstUserTypeFound = false;
                $queryArray = array();
                for ($i = 0; $i < count($userTypes); $i++) {
                    $queryArray [$i] = [
                        ['user_types.user_type', '=', $userTypes[$i]],
                    ];
                    if (!$blnFirstUserTypeFound) {
                        $blnFirstUserTypeFound = true;
                        $q->where([$queryArray]);
                    } else {
                        $q->orWhere([$queryArray]);
                    }
                    $queryArray = array();
                }
            });
        } else if ($flgUserTypeStatuses) {
            /* otherwise, we just have a userTypeStatus filter and no userTypes to worry about */
            if ($filters->userTypeStatusQuery !== null) {
                $userList->whereHas('userUserType.userTypeStatus', function ($q) use ($userList, $userTypeStatuses, $filters) {
                    $blnFirstUserTypeFound = false;
                    $queryArray = array();
                    if (count($userTypeStatuses) > 0) {
                        for ($j = 0; $j < count($userTypeStatuses); $j++) {
                            $queryArray [$j] = [
                                ['user_type_statuses.user_type_status', '=', $userTypeStatuses[$j]]
                            ];
                            if (!$blnFirstUserTypeFound) {
                                $blnFirstUserTypeFound = true;
                                $q->where([$queryArray]);
                            } else {
                                $q->orWhere([$queryArray]);
                            }
                            $queryArray = array();

                        }
                    }
                });
            }

        }
    }
}
