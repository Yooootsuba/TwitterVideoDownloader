<?php


require __DIR__ . '/vendor/autoload.php';


class TwitterDownloader
{

    function __construct($video_url)
    {
        $this->video_url = $video_url;

    }

}


$twitter_downloader = new TwitterDownloader('hello');


?>
