<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;

use Illuminate\Http\Request;

class AuthController extends ApiController {

    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
     * @return void
     */
    public function __construct(Guard $auth, Registrar $registrar)
    {
        //$this->auth = $auth;
        $this->registrar = $registrar;

        //$this->middleware('guest', ['except' => 'getLogout']);
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
        $validator = $this->registrar->validator($request->all());

        if ($validator->fails()){
            return $this->respondBadRequest($validator->errors());
        }

        $this->registrar->create($request->all());
        return $this->respondCreated('Registration Successful');
	}

    public function oauthStore(){

    }

}
