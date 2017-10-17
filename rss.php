<?php

spl_autoload_register(function ($class) {
    require_once ('classes/' . $class . '.php');
});


if (!empty($_GET) && isset($_GET['user']))
{
    $rssXML = new SimpleXMLElement("<rss></rss>");
    $rssXML->addAttribute("version", "2.0");
    $channel = $rssXML->addChild("channel");
    $channel->addChild("title", "User image list");
    $channel->addChild("description", "description");
    $channel->addChild("link", "http://localhost:8888/MAMP/projects/420px/index.php?user=" . $_GET['user']);

    $user = $channel->addChild("item");
    $user->addAttribute("name", $_GET['user']);

    $u = new User();
    $imageList = $u->getImageList($_GET['user']);

    $user_image_path = "http://localhost:8888/MAMP/projects/420px/photos" . DIRECTORY_SEPARATOR . $_GET['user'];

    foreach($imageList as $image)
    {
        $user->addChild("image", $user_image_path . DIRECTORY_SEPARATOR .$image);
    }

    Header('Content-type: text/xml');
    echo $rssXML->asXML();
}