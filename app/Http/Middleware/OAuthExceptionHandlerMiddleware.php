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
		//return $next($request);
        try {

            return $next($request);

        } catch (OAuthException $e) {
            return $this->setStatusCode($e->httpStatusCode)->respondWithError([[//TODO: Fix nested arrays for JSON standardisation :(
                'id' => '',
                'title' => $e->errorType,
                'detail' => $e->getMessage(),
                'status' => $e->httpStatusCode,
                'code' => ''
            ]]);
        }

	}

}


