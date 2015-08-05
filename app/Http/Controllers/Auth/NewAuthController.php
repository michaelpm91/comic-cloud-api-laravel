<?php namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\User;
use Validator;
use Response;
use Authorizer;

class NewAuthController extends ApiController {

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $request
     * @return User
     */
    protected function create(Request $request)
    {
        $request = $request->all();

        $validator = Validator::make($request, [
            'username' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()){
            $pretty_errors = array_map(function($item){
                return [
                    'title' => 'Missing Required Field',
                    'detail' => $item,
                    'status' => 400,
                    'code' => ''
                ];
            }, $validator->errors()->all());

            return $this->respondBadRequest($pretty_errors);
        }

        User::create([
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
        ]);

        return $this->respondCreated('Registration Successful');//TODO: Should this be a more meaning message?
    }

    protected function createToken(){
        return Response::json(Authorizer::issueAccessToken());
    }

}