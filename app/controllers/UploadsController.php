<?php

class UploadsController extends BaseController {

    public function index(){
        $uploads = Upload::all();

        //return $this->response->array($uploads->toArray());
        //return $this->response->collection($uploads, new UploadTransformer);
        return $this->response->noContent();
    }

    public function show($id)
    {
        $upload = Upload::findOrFail($id);

        return $this->response->array($upload->toArray());
        //return $this->response->item($upload, new UploadTransformer);
        //return $this->response->noContent();
        //return $this->response->item($upload, new UploadTransformer);
    }

    public function store(){

    }

}
