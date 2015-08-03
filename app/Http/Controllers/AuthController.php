<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;

use Illuminate\Http\Request;

class AuthController extends ApiController {

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
        $validator = Registrar::validator($request->all());

        if ($validator->fails()){
            return $this->respondBadRequest($validator->errors());
        }

        Registrar::create($request->all());
        return $this->respondCreated('Registration Successful');
	}

}
