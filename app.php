<?php


use Chrisyue\PhpM3u8\Stream\TextStream;
use Chrisyue\PhpM3u8\Facade\ParserFacade;


require __DIR__ . '/vendor/autoload.php';


class TwitterDownloader
{

    function __construct($video_url)
    {
        $this->video_url    = $video_url;
        $this->video_url_id = str_replace('https://twitter.com/i/status/', '', $video_url);
        $this->segments     = array();
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

    function get_segments()
    {
        # Get video config
        $response = Requests::get('https://api.twitter.com/1.1/videos/tweet/config/' . $this->video_url_id . '.json', $this->headers);
        $json = json_decode($response->body, true);

        # Get playback url
        $playback_url = $json['track']['playbackUrl'];
        $response = Requests::get($playback_url);

        # Create a m3u8 parser
        $m3u8_parser = new ParserFacade();

        # Get m3u8 url
        $m3u8_list = $m3u8_parser->parse(new TextStream($response->body));
        $m3u8_url = 'https://video.twimg.com' . end($m3u8_list['EXT-X-STREAM-INF'])['uri'];

        # Get video segments
        $response = Requests::get($m3u8_url);
        $segments = $m3u8_parser->parse(new TextStream($response->body));
        foreach ($segments['mediaSegments'] as $segment)
        {
            array_push($this->segments, 'https://video.twimg.com' . $segment['uri']);
        }
    }

    function download()
    {
        $this->get_bearer_token();
        $this->get_guest_token();
        $this->get_segments();

        $file_m = fopen('merge_list', 'w+');
        foreach ($this->segments as $count => $segment)
        {
            $response = Requests::get($segment);
            $file = fopen((string)$count . '.ts', 'w+');
            fwrite($file, $response->body);
            fclose($file);

            fwrite($file_m, 'file ' . (string)$count . '.ts' . "\n");
        }
        fclose($file_m);

        $this->merge();
    }

    function merge()
    {
        system('ffmpeg -f concat -i merge_list -c copy output.mp4');
        $this->clean_segment_files();
    }

    function clean_segment_files()
    {
        foreach ($this->segments as $count => $segment) {
            unlink((string)$count . '.ts');
        }
    }

}


echo 'Enter a Twitter video url : ';
$video_url = trim(fgets(STDIN));
$twitter_downloader = new TwitterDownloader($video_url);
$twitter_downloader->download();


?>
