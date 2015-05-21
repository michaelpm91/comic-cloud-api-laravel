<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Contracts\Routing\ResponseFactory;

use LucaDegasperi\OAuth2Server\Authorizer;

use App\User;

class ApiController extends Controller {
    //const HTTP_NOT_FOUND = 404;
    //self::HTTP_NOT_FOUND

    protected $currentUser;
    protected $authorizer;

    public function __construct(Authorizer $authorizer = null){
        $this->authorizer = $authorizer;
        $this->currentUser = null;

        if( ( null !== $authorizer->getChecker()->getAccessToken()) ){
            $uid = $this->authorizer->getResourceOwnerId();
            $this->currentUser = User::find($uid);
        }
    }

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @param mixed $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = 'Not Found'){
        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError($message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondInternalError($message = 'Internal Error'){
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondBadRequest($message = 'Internal Error'){
        return $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)->respondWithError($message);
    }


    /**
     * @param $message
     * @return mixed
     */
    protected function respondSuccessful($message)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respond([
            'message' => $message
        ]);
    }

    /**
     * @param $message
     * @return mixed
     */
    protected function respondCreated($message)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)->respond([
            'message' => $message
        ]);
    }


    /**
     * @param $message
     * @return mixed
     */
    protected function respondNoContent($message)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_NO_CONTENT)->respond([
            'message' => $message
        ]);
    }

    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = []){
        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param $message
     * @return mixed
     */
    public function respondWithError($message){
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

}
