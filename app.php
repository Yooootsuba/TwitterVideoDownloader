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
        $this->headers      = array();
    }

    function get_bearer_token()
    {
        $response = Requests::get('https://abs.twimg.com/web-video-player/TwitterVideoPlayerIframe.cefd459559024bfb.js');
        preg_match('/Bearer ([a-zA-Z0-9%-])+/', $response->body, $matches);
        $bearer_token = $matches[0];
        $this->headers['Authorization'] = $bearer_token;
    }

    function get_guest_token()
    {
        $response = Requests::post('https://api.twitter.com/1.1/guest/activate.json', $this->headers);
        preg_match('/[0-9]+/', $response->body, $matches);
        $guest_token = $matches[0];
        $this->headers['x-guest-token'] = $guest_token;
    }
}


$twitter_downloader = new TwitterDownloader('https://twitter.com/i/status/1201327902941253632');
$twitter_downloader->get_bearer_token();
$twitter_downloader->get_guest_token();

?>
