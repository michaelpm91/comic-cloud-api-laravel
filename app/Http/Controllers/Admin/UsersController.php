<?php namespace App\Http\Controllers\Admin;

/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 08/08/15
 * Time: 21:47
 */

use App\Http\Controllers\ApiController;
use App\Models\Admin\User;


class UsersController extends ApiController {

    /**
     * @return mixed
     */
    public function index(){

        $users = User::paginate(env('paginate_per_page'))->toArray();//TODO: Filter admins?

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

        $user = User::find($id);


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

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id){

        $user = User::find($id);

        if($user){

            $data = Request::all();

            //TODO: Edit User Data

            if(empty($data)) return $this->respondBadRequest([[
                'title' => 'No Data Sent',
                'detail' => 'No Data Sent',
                'status' => 400,
                'code' => ''
            ]]);

            if (isset($data['series_title'])) {
                $user->email = $data['email'];
                $user->save();
            }
            return $this->respondSuccessful([
                'user' => [$user]
            ]);

        }else{
            return $this->respondNotFound([
                'title' => 'User Not Found',
                'detail' => 'User Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){

        $user = User::find($id);

        if($user){
            $user->delete();
            return $this->respondSuccessful('User Deleted');

        }else{
            return $this->respondNotFound([
                'title' => 'User Not Found',
                'detail' => 'User Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

    }




}
