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

            /*return new JsonResponse([
                    'error'             => $e->errorType,
                    'error_description' => $e->getMessage()
                ],
                $e->httpStatusCode,
                $e->getHttpHeaders()
            );*/

            return $this->setStatusCode($e->httpStatusCode)->respondWithError([
                'id' => '',
                'title' => $e->errorType,
                'detail' => $e->getMessage(),
                'status' => $e->httpStatusCode,
                'code' => ''
            ]);
        }

	}

}


