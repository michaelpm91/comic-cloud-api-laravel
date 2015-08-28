<?php namespace App\Http\Controllers;

use App\Http\Requests;

use Illuminate\Http\Response as IlluminateResponse;

use LucaDegasperi\OAuth2Server\Authorizer;

use App\Models\User;

class ApiController extends Controller {

    protected $currentUser = null;
    protected $currentUserType = null;
    protected $authorizer;

    public function __construct(Authorizer $authorizer = null){
        $this->authorizer = $authorizer;

        if( ( null !== $authorizer->getChecker()->getAccessToken()) ){
            if($this->authorizer->getResourceOwnerType() == "user"){
                $uid = $this->authorizer->getResourceOwnerId();
                $this->currentUser = User::find($uid);
                $this->currentUserType = $this->currentUser->type;
                return;
            }else if($this->authorizer->getResourceOwnerType() == "client"){
                return;
            }
        }

        //if(env('DB_DRIVER') == 'sqlite_in_memory') DB::statement('PRAGMA foreign_keys = ON');//TODO: For testing/local comic cloud perhaps

    }

    /**
     * @var int
     */
    protected $statusCode = 200;
    protected $statusMessage = 'success';
    protected $message = null;

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
        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respond($message);
    }
    /**
     * @param $message
     * @return mixed
     */
    protected function respondCreated($message)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)->respond($message);
    }

    public function respondNoContent(){

        return $this->setStatusCode(IlluminateResponse::HTTP_NO_CONTENT)->respond();
    }

    public function respondUnauthorised($message = "Unauthorised Request"){

        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respond($message);
    }
    /**
     * @param $errors_object
     * @internal param $message
     * @return mixed
     */
    public function respondWithError($errors_object){
        return $this->respond([
            'errors' => $errors_object
        ]);
    }
    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data = [], $headers = []){
        return response()->json($data, $this->getStatusCode(), $headers);
    }
}
