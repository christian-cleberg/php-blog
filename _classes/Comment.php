<?php


class Comment
{
    function __construct(string $comment, string $postURL, string $username = 'Anonymous')
    {
        $this->timestamp = date('Y-m-d H:i:s');
        $this->username = $username;
        $this->comment = $comment;
        $this->postURL = $postURL;
    }

    function saveComment(string $fileName)
    {
        if (file_exists($fileName)) {
            $sourceData = file_get_contents($fileName);
            $tempArray = json_decode($sourceData);
            array_push($tempArray, $this);
            $jsonData = json_encode($tempArray, JSON_PRETTY_PRINT);
            file_put_contents($fileName, $jsonData);
        } else {
            die('Error: The ' . $fileName . ' file does not exist.');
        }
    }
}