<?php

class CollectionsController extends ApiController {

    protected $extractLocation = '/var/www/dev/processingPath/';

    protected $resizeLocation = '/var/www/dev/resizePath/';

    protected $user_id;

    protected $collection_id;

    protected $comic_id;

    //protected $user_data;

    public function createComic($fileName, $collection){

        $comic_info = $this->getComicInfo($fileName);

        $comic = new Comic;
        $comic->comic_issue = $comic_info['issue'];
        $comic->comic_writer = $comic_info['comic_writer'];
        $comic->comic_collection = (($collection->collection_contents ? $collection->collection_contents : '' ));
        $comic->user_id = $this->user_id;
        $comic->series_id = $this->createSeries($comic_info['series_title']);
        $comic->collection_id = $collection->id;
        $comic->comic_status = $collection->collection_status;
        $comic->save();

        $this->comic_id = $comic->id;

    }

    public function createSeries($seriesTitle){

        $series = User::find($this->user_id)->first()->series()->where('series_title', '=', $seriesTitle)->first();

        if(!$series){
            $series = new Series;
            $series->series_title = $seriesTitle;
            $series->series_start_year = '0000';
            $series->series_publisher = 'Unknown';
            $series->user_id = $this->user_id;
            $series->save();
        }

        return $series->id;

    }

    public function getComicInfo($filename){
        $seriesTitle = 'Unknown';

        $seriesPreg = ' Vol.[0-9]+| #[0-9]+|\(.*?\)|\.[a-z0-9A-Z]+$';
        $tempSeriesTitle = trim(preg_replace('/'.$seriesPreg.'/', "", $filename));
        if($tempSeriesTitle) $seriesTitle = $tempSeriesTitle;

        $comicInfo = ['issue' => 1, 'comic_writer' => 'Unknown','series_title' => $seriesTitle]; //Default array

        //$comicInfo = ['issue' => 1, 'comic_writer' => 'Unknown','series_title' => 'Unknown']; //Default array

        return $comicInfo;
    }

    public function processArchive($data){

        Log::info('Reaching Process Archive');

        $file = $this->extractLocation.$data['newFileName'];

        $s3 = AWS::get('s3');
        $result = $s3->getObject(array(
            'Bucket' => 'comicclouduploads',
            'Key'    => $data['newFileName'],
            'SaveAs' => $file
        ));
        Log::info('File is: '.$file);
        //mkdir($this->extractLocation.$data['newFileNameNoExt']);

        $this->extractArchive($file, $data['newFileNameNoExt'], $data['fileExt']);

    }

    public function extractArchive($file, $fileNoExt, $fileExtension){//todo-mike: Need to make this more DRY. Also PDFs and virus checks...

        if(in_array($fileExtension, array('zip', 'cbz'))){

            $zip = new ZipArchive;

            if ($zip->open($file) === true) {

                mkdir($this->extractLocation.$fileNoExt);
                $pages = [];
                for($i = 0; $i < $zip->numFiles; $i++) {

                    $entry = $zip->getNameIndex($i);

                    if ( substr( $entry, -1 ) == '/' ) continue; // skip directories

                    $entryExt = strtolower(pathinfo(basename($entry), PATHINFO_EXTENSION));
                    $acceptedExtensions = ['jpg', 'jpeg'];
                    if (!in_array($entryExt, $acceptedExtensions)) continue; //skip non-jpegs

                    $zip->extractTo($this->extractLocation.$fileNoExt, array($entry));

                    $image_slug = $this->processImage($this->extractLocation.$fileNoExt."/".basename($entry));
                    $pages[$image_slug] = basename($entry);

                }
                $zip->close();

                //unlink($file);

                natsort($pages);
                $pages = array_flip($pages);
                $pages = array_values($pages);

                array_unshift($pages, 'presentation_value');
                unset($pages[0]);//Add and remove value at zero to shift array to 1. Just for presentation.

                //json_encode($pages);

                $collection = Collection::find($this->collection_id);

                $collection->collection_contents = json_encode($pages, JSON_FORCE_OBJECT);

                $collection->collection_status = 1;

                $collection->save();

                $comic = Comic::find($this->comic_id);

                $comic->comic_collection = json_encode($pages, JSON_FORCE_OBJECT);

                $comic->comic_status = 1;

                $comic->save();
            }

        }else if(in_array($fileExtension, array('rar', 'cbr'))){

            $x = rar_open($file);

            if($x == true){

                mkdir($this->extractLocation.$fileNoExt);
                $pages = [];

                $entries = rar_list($x);

                foreach ($entries as $key => $entry) {

                    if ( substr( $entry, -1 ) == '/' ) continue; // skip directories

                    $entryExt = strtolower(pathinfo(basename($entry->getName()), PATHINFO_EXTENSION));
                    $acceptedExtensions = ['jpg', 'jpeg'];

                    if (!in_array($entryExt, $acceptedExtensions)) continue; //skip non-jpegs

                    $file = basename($entry->getName());

                    if (!in_array($entryExt, $acceptedExtensions)) continue; //skip non-jpegs

                    $entry->extract( false , $this->extractLocation.$fileNoExt.'/'.$file);

                    $image_slug = $this->processImage($this->extractLocation.$fileNoExt."/".$file);
                    $pages[$image_slug] = $file;

                }

                rar_close($x);

                natsort($pages);
                $pages = array_flip($pages);
                $pages = array_values($pages);

                array_unshift($pages, 'presentation_value');
                unset($pages[0]);//Add and remove value at zero to shift array to 1. Just for presentation.

                //json_encode($pages);

                $collection = Collection::find($this->collection_id);

                $collection->collection_contents = json_encode($pages, JSON_FORCE_OBJECT);

                $collection->collection_status = 1;

                $collection->save();

                $comic = Comic::find($this->comic_id);

                $comic->comic_collection = json_encode($pages, JSON_FORCE_OBJECT);

                $comic->comic_status = 1;

                $comic->save();

            }
        }
    }

    public function processImage($image){

        $fileHash = hash_file('md5', $image);

        $imageentry = ComicImage::where('image_hash', '=', $fileHash)->first();


        if(!$imageentry){

            $imageExt = strtolower(pathinfo($image, PATHINFO_EXTENSION));

			Log::info('Image Process');

			$image_slug = str_random(40);

            $sizes = [
                'medium' => [
                    'width' => 673, 'height' => 1037
                ],
                'thumbnail' => [
                    'width' => 222, 'height' => 340
                ]
            ];

            foreach ($sizes as $size => $dimensions){

                $tempLoc = $this->resizeLocation.str_random(80);
                $img = Image::make($image)->resize(null, $dimensions['height'], function ($constraint) { $constraint->aspectRatio(); })->save($tempLoc);

                $s3 = AWS::get('s3');
                $result = $s3->putObject(array(
                    'Bucket'     => 'comiccloudimages',
                    'Key'        => $image_slug."_".$size.".".$imageExt,
                    'SourceFile' => $tempLoc,
                    'ACL'        => 'public-read',
                ));
            }

            $imageentry = new ComicImage;

            $imageentry->image_slug = $image_slug;// = str_random(10);
            $imageentry->image_hash = $fileHash;
            $imageentry->image_size = filesize($image);
            $imageentry->save();

        }
        $imageentry->collections()->attach($this->collection_id);

        return $imageentry->image_slug;
    }

    public function fire($job, $data){

        Log::info('Firing.');
        $this->user_id = $data['user_id'];
        $collection = Collection::where('collection_hash', '=', $data['hash'])->first();
        $prcoessArchive = false;

        if(!$collection){
            $collection = new Collection;
            $collection->upload_id = $data['upload_id'];
            $collection->collection_hash = $data['hash'];
            $collection->collection_status = 0;
            $collection->save();
            $prcoessArchive = true;
        }

        $this->collection_id = $collection->id;

        $this->createComic($data['newFileNameNoExt'], $collection);

        if($prcoessArchive){
            Log::info('Process Archive');
            $this->processArchive($data);
        }

        $job->delete();

    }

}

//Queue::push('CollectionsController', array('upload_id' => $upload->id,'hash'=> $fileHash, 'newFileName' => $newFileName,'newFileNameNoExt' => $newFileNameNoExt, 'fileExt' => $file->getClientOriginalExtension(),'time' => time()));
