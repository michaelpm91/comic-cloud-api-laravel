<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class ComicImagesTableSeeder extends Seeder {

    public function run()
    {
        $faker = Faker::create();

        $collectionIDs = Collection::lists('id');

        $categoryArray = ['abstract','animals','business','cats','city','food','nightlife','fashion','people','nature','sports','technics','transport'];

        $sizesArray = [
            /*'Large' =>
                [ 'width' => 1275, 'height' => 1650 ],*/
            'Medium' =>
                [ 'width' => 956, 'height' => 1237 ],
            /*'Small' =>
                [ 'width' => 637, 'height' => 825 ],
            'Thumbnail' =>
                [ 'width' => 318, 'height' => 412 ]*/
        ];

        foreach($collectionIDs as $collectionID){

            $collection_contents = [];
            foreach(range(1,rand(15,25)) as $index){
                $image_set_key = str_random(5);
                $imageSubject = $faker->randomElement($categoryArray);
                $collection_contents[$index] = $image_set_key;
                foreach($sizesArray as $size => $resolution){

                    $ComicImage = ComicImage::create([
                        'image_set_key' => $image_set_key,
                        'image_size' => $size,
                        'image_hash' => $faker->md5,
                        'image_url' => $faker-> imageUrl($resolution['width'], $resolution['height'], $imageSubject),
                        'collection_id' => $collectionID
                    ]);
                    //$this->command->info('ID: ' . $ComicImage->id);

                }
            }

            $collection = Collection::find($collectionID);

            $collection->collection_contents = json_encode($collection_contents);

            $collection->save();

        }
    }

}
