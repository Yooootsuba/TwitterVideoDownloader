<?php


require __DIR__ . '/vendor/autoload.php';
use PHPHtmlParser\Dom;


class TwitterDownloader
{

    function __construct($video_url)
    {
        $this->video_url    = $video_url;
        $this->video_url_id = str_replace('https://twitter.com/i/status/', '', $video_url);
        $this->session      = new Requests_Session();
    }

    function get_bearer_token()
    {
        $response = $this->session->get('https://abs.twimg.com/web-video-player/TwitterVideoPlayerIframe.cefd459559024bfb.js');
        preg_match('/Bearer ([a-zA-Z0-9%-])+/', $response->body, $matches);
        $bearer_token = $matches[0];
    }

}


$twitter_downloader = new TwitterDownloader('https://twitter.com/i/status/1201327902941253632');
$twitter_downloader->get_bearer_token();


?>
