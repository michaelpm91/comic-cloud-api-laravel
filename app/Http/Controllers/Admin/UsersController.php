<?php namespace App\Http\Controllers\Admin;

/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 08/08/15
 * Time: 21:47
 */

use App\Http\Controllers\ApiController;
use App\AdminUser;


class UsersController extends ApiController {

    /**
     * @return mixed
     */
    public function index(){

        $users = AdminUser::paginate(env('paginate_per_page'))->toArray();//TODO: Filter admins?

        $users['user'] = $users['data'];
        unset($users['user']);

        return $this->respond($users);
    }

    /**
     * Display the specified upload.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {

        $user = AdminUser::find($id);


        if(!$user){
            return $this->respondNotFound([
                'title' => 'User Not Found',
                'detail' => 'User Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

        return $this->respond([
            'user' => [$user]
        ]);
    }


}
