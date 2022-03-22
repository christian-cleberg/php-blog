<?php

function submitComment()
{
    // Get the content sent from the comment form
    $comment = htmlentities($_POST['userContent']);
    $postURL = $_POST['postURL'];

    // Set default values if blank
    if (isset($_POST['userName']) && trim($_POST['userName']) !== "") {
        $username = $_POST['userName'];
    } else {
        $username = null;
    }

    // Create a 'Comment' object
    include_once('_classes/Comment.php');
    $userComment = new Comment($comment, $postURL, $username);

    // Append comment to JSON file
    $userComment->saveComment('_data/comments.json');

    // Send the user back
    header('Location: ' . $postURL . '#comments');
}