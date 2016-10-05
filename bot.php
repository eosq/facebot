<?php
require_once 'HTTP/Request2.php';
require_once 'twitteroauth/autoload.php'; //https://github.com/abraham/twitteroauth
use Abraham\TwitterOAuth\TwitterOAuth;
//https://apps.twitter.com/
$CONSUMER_KEY=''; // Consumer Key
$CONSUMER_SECRET=''; //	Consumer Secret
$oauth_tok=''; //	Access Token
$oauth_sec=''; //	Access Token Secret
$tweet_id=''; // tweet id. example 783744302799286272


$request = new Http_Request2('https://api.projectoxford.ai/face/v1.0/detect');
$url = $request->getUrl();
$headers = array(
    'Content-Type' => 'application/json',
    'Ocp-Apim-Subscription-Key' => 'key', // get key https://www.microsoft.com/cognitive-services/
);
$request->setHeader($headers);
$parameters = array(
    'returnFaceId' => 'true',
    'returnFaceLandmarks' => 'false',
    'returnFaceAttributes' => 'age,gender',
);
$url->setQueryVariables($parameters);
$request->setMethod(HTTP_Request2::METHOD_POST);

$connection = new TwitterOAuth($CONSUMER_KEY,$CONSUMER_SECRET,$oauth_tok,$oauth_sec);
$tweet=$connection->get('statuses/show', array('id'=>$tweet_id));

    if(!isset($tweet->errors) && $tweet->entities->media[0]->media_url!=null &&
    $tweet->entities->media[0]->type=='photo') {
    $photoURL=$tweet->entities->media[0]->media_url;
    $request->setBody('{"url":"'.$photoURL.'"}');
    try
    {
        $response = $request->send();
        $face=$response->getBody();
    }
    catch (HttpException $ex)
    {
        echo $ex;
    }
    $face=json_decode($face, true);
    $count_people=count($face);
    if($face!=NULL && $count_people<=4) {
    echo '<p>find '.$count_people.'</p>';
    echo '<p><img src="'.$photoURL.'"></p>';
    $screen_name=$tweet->user->screen_name;
    $text='@'.$screen_name.' cкорее всего на фото';
    $im=imagecreatefromjpeg($photoURL);
    $ink = imagecolorallocate($im, 51, 51, 204);

foreach ($face as $fac) {
  $gender=$fac["faceAttributes"]["gender"];
  $age=$fac["faceAttributes"]["age"];
  $age=round($age);
    if($gender=='male') {
      if($age<=18) {

        $text.=' мальчик и ему '.$age.' '.num2word($age, array('год', 'года', 'лет')).' ';
      }
      elseif($age>=19 && $age<=69) {
        $text.=' мужчина и ему '.$age.' '.num2word($age, array('год', 'года', 'лет')).' ';
      }
      else {
        $text.=' дедуля и ему '.$age.' '.num2word($age, array('год', 'года', 'лет')).' ';
      }
                          }

    elseif($gender=='female') {
      if($age<=18) {
      $text.=' девочка и ей '.$age.' '.num2word($age, array('год', 'года', 'лет')).' ';
      }
      elseif($age>=19 && $age<=29) {
        $text.=' девушка и ей '.$age.' '.num2word($age, array('год', 'года', 'лет')).' ';
      }
      elseif($age>=30 && $age<=59) {
        $text.=' женщина и ей '.$age.' '.num2word($age, array('год', 'года', 'лет')).' ';
      }
      else {
        $text.=' бабуля и ей '.$age.' '.num2word($age, array('год', 'года', 'лет')).' ';
      }
    }

    $top=$fac["faceRectangle"]["top"];
    $left=$fac["faceRectangle"]["left"];
    $width=$fac["faceRectangle"]["width"];
    $height=$fac["faceRectangle"]["height"];
    $size=5;
    for($i=0;$i<$size;$i++) {
    imagerectangle($im,$left-$i,$top-$i,$left+$width+$i,$top+$height+$i,$ink);
    }
}
    echo $text.'<br>';
    $path= 'image/'.$tweet->id_str.'.jpg';
    imagejpeg($im, $path,85);
    imagedestroy($im);
  $result=$connection->send($text,$tweet->id_str,$path);

  }
    }


  function num2word($num, $words)
{
    $num = $num % 100;
    if ($num > 19) {
        $num = $num % 10;
    }
    switch ($num) {
        case 1: {
            return($words[0]);
        }
        case 2: case 3: case 4: {
            return($words[1]);
        }
        default: {
            return($words[2]);
        }
    }
}

  ?>
