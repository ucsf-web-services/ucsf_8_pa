<?php
namespace Drupal\twitter_tweets\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\twitter_tweets\twitter_api_php\TwitterAPIExchange;
/**
* Provides a block for executing PHP code.
 *
 * @Block(
 *   id = "twitter_tweets_block",
 *   admin_label = @Translation("Twitter Tweets")
 * )
 */
class TweetsBlock extends BlockBase {  /**
   * Builds and returns the renderable array for this block plugin.
   *
   * @return array
   *   A renderable array representing the content of the block.
   *
   * @see \Drupal\block\BlockViewBuilder
   */
  public function build() {
    $config = \Drupal::config('twitter_tweets.credentials');
    $settings = array();
    $settings['oauth_access_token'] = $config->get('oauth_access_token');
    $settings['oauth_access_token_secret'] = $config->get('oauth_access_token_secret');
    $settings['consumer_key'] = $config->get('consumer_key');
    $settings['consumer_secret'] = $config->get('consumer_secret');
    $screen_name = $config->get('screen_name');
    $tweet_count = $config->get('tweet_count');
    $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $getfield = '?screen_name='.$screen_name.'&count=' . $tweet_count.'&tweet_mode=extended';
    $requestMethod = 'GET';
    $twitter = new TwitterAPIExchange($settings);
    $tweets = $twitter->setGetfield($getfield) ->buildOauth($url, $requestMethod)->performRequest();

    $tweets = json_decode($tweets);
    //https://benmarshall.me/parse-twitter-hashtags/

    $reg_Url = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
    //$reg_Url = "/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~\?\/.=]+/";
    //http://twitter.com/' + username
    $reg_User = "/([@]+[A-Za-z0-9-_]+)/i";
    // http://search.twitter.com/search?q= + tag
    $reg_Hash = "/[#]+[A-Za-z0-9-_]+/i";

    //dvm($tweets);

    foreach($tweets as $tweet) {
      //$tweet->full_text = check_markup($tweet->full_text, 'full_html');
      //$tweet->full_text = '   ';
      $tweet->full_text = preg_replace('~[\r\n]~', ' ', $tweet->full_text);
      if($tweet->retweeted_status!=null){
        $retweet_urls = $tweet->retweeted_status->entities->urls;
        foreach($retweet_urls as $retweet_url) {
          $pattern = $retweet_url->url;
          $pattern = preg_replace("/https:\/\/t\.co\//","",$pattern);
          $pattern = "/https:\/\/t\.co\/$pattern/i";
          if(preg_match($pattern, $tweet->retweeted_status->full_text, $url)) {
            $tweet->retweeted_status->full_text =  preg_replace($pattern, "<a href=\"{$url[0]}\">{$url[0]}</a>", $tweet->retweeted_status->full_text);
          }
        }
        $retweet_media = $tweet->retweeted_status->entities->media;
        if($retweet_media){
          $pattern = $retweet_media[0]->url;
          $pattern = preg_replace("/https:\/\/t\.co\//","",$pattern);
          $pattern = "/https:\/\/t\.co\/$pattern/i";
          if(preg_match($pattern, $tweet->retweeted_status->full_text, $url)) {
            $tweet->retweeted_status->full_text =  preg_replace($pattern, "<a href=\"{$url[0]}\">{$url[0]}</a>", $tweet->retweeted_status->full_text);
          }
        }
      }
      if(preg_match($reg_Url, $tweet->full_text, $url)) {
        $tweet->full_text =  preg_replace($reg_Url, "<a href=\"{$url[0]}\">{$url[0]}</a>", $tweet->full_text);
      }
      
      if($tweet->retweeted_status!=null){
        if(preg_match_all($reg_User, $tweet->retweeted_status->full_text, $url)) {
          foreach($url[0] as $user){
            $tweet->retweeted_status->full_text =  preg_replace("/$user /", "<a href=\"http://twitter.com/{$user}\">{$user} </a>", $tweet->retweeted_status->full_text);
          }
        }
      }
      if(preg_match_all($reg_User, $tweet->full_text, $url)) {
        foreach($url[0] as $user){
          $tweet->full_text =  preg_replace("/$user /", "<a href=\"http://twitter.com/{$user}\">{$user} </a>", $tweet->full_text);
        }
      }
      if($tweet->retweeted_status!=null){
        if(preg_match_all($reg_Hash, $tweet->retweeted_status->full_text, $url)) {
           foreach($url[0] as $hash){
             $tag = substr($hash,1);
             $tweet->retweeted_status->full_text =  preg_replace("/$hash /", "<a href=\"https://twitter.com/hashtag/{$tag}?src=hash\">{$hash} </a>", $tweet->retweeted_status->full_text);
           }
        }
      }
      if(preg_match_all($reg_Hash, $tweet->full_text, $url)) {
        foreach($url[0] as $hash){
          $tag = substr($hash,1);
          $tweet->full_text =  preg_replace("/$hash /", "<a href=\"https://twitter.com/hashtag/{$tag}?src=hash\">{$hash} </a>", $tweet->full_text);
        }
      }

      //$tweet->full_text = preg_replace('/https:\/\/t.co\/.*/' ,'' ,$tweet->full_text);
      $cleanTweets[] = $tweet;
    }

    $params = array('tweets' => $cleanTweets);
    $tweet_template = array('#theme' => 'twitter_tweets_tweet_listing', '#params' => $params);
    return $tweet_template;
 }
 }
