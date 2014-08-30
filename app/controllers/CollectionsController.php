<?php

class CollectionsController extends ApiController {

    //protected $extractLocation = base_path().'/processingPath/';

    public function createComic($collection_id){
        Log::info('created with id: ' . $collection_id);
    }

    public function createSeries(){

    }

    public function processArchive($data){

        /*$s3 = AWS::get('s3');
        $result = $s3->getObject(array(
            'Bucket' => 'comicclouduploads',
            'Key'    => $data['newFileName'],
            'SaveAs' => base_path().'/processingPath/'.$data['newFileName']
        ));*/

    }

    public function fire($job, $data){

        Log::info('Firing.');

        $collection = Collection::where('collection_hash', '=', $data['hash'])->first();

        if(!$collection){

            $collection = new Collection;
            $collection->upload_id = $data['upload_id'];
            $collection->collection_hash = $data['hash'];
            $collection->save();

            $this->processArchive($data);
        }

        $this->createComic($collection->id);

        $job->delete();

    }

}

//Queue::push('CollectionsController', array('upload_id' => $upload->id,'hash'=> $fileHash, 'newFileName' => $newFileName,'newFileNameNoExt' => $newFileNameNoExt, 'fileExt' => $file->getClientOriginalExtension(),'time' => time()));

