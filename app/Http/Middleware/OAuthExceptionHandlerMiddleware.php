<?php namespace App\Http\Middleware;

use Closure;
use League\OAuth2\Server\Exception\OAuthException;
use App\Http\Controllers\ApiController;

class OAuthExceptionHandlerMiddleware extends ApiController {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        try {
            return $next($request);
        } catch (OAuthException $e) {
            //TODO: Find a cleaner way to do this. Probably overriding package stuff. Maybe //App::abort(401, 'Unauthorized action.');
            $error_message = $e->getMessage();
            if($e->errorType == "invalid_request" && $e->getMessage() == "The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed. Check the \"access token\" parameter.") {
                $e->errorType = "access_denied";
                 $error_message = "The resource owner or authorization server denied the request.";
                $e->httpStatusCode = 401;
            }
            //TODO: Catch Oauth scope, grant, client mismatch error
            return $this->setStatusCode($e->httpStatusCode)->respondWithError([[//TODO: Fix nested arrays for JSON standardisation :(
                'title' => $e->errorType,
                'detail' => str_replace('"', "", $error_message),//TODO: Maybe escape this appropriately
                'status' => $e->httpStatusCode,
                'code' => ''
            ]]);
        }
	}

}


