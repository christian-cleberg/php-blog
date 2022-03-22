<?php

function loadHomepageJSON(string $fileName): string
{
    // Get the file contents and decode JSON
    $fileContents = file_get_contents($fileName);
    $data = json_decode($fileContents);

    // Loop through the JSON object
    $stack = [];
    $metadata = '';
    foreach ($data as $postObject) {
        // Only load published posts
        if ($postObject->published == 'Yes') {
            // Add relevant metadata to the output template
            $year = date_format(date_create($postObject->created), "Y");
            if (!in_array($year, $stack)) {
                // If any posts have been created in a list, close the existing <ul> and open a new <ul> after the heading
                if (!empty($stack)) {
                    $metadata .= '</ul>';
                }
                $metadata .= '<h2 class="text-center">' . $year . '</h2><ul class="blog-posts">';
                array_push($stack, $year);
            }
            $metadata .= '<li><time datetime=' . date_format(date_create($postObject->created), "Y-m-d") . '>' . date_format(date_create($postObject->created), "Y-m-d") . '</time><a href=' . $postObject->link . ' aria-label="Read ' . $postObject->title . ' blog post">' . $postObject->title . '</a></li>';
        }
    }
    $metadata .= '</ul>';
    return $metadata;
}

function loadCategoryJSON(string $fileName): string
{
    // Get the file contents and decode JSON
    $fileContents = file_get_contents($fileName);
    $data = json_decode($fileContents);

    // Sort the JSON data by the `tag`, then by `create`.
    // I had to sort a -> b to get an "A-Z" sort for `tag`,
    // but b -> a to get a "first-last" sort for `create`.
    uasort($data, function ($a, $b) {
        return strcmp($a->tag, $b->tag) ?: strcmp($b->created, $a->created);
    });

    // Loop through the JSON object
    $stack = [];
    $metadata = '';
    foreach ($data as $postObject) {
        // Only load published posts
        if ($postObject->published == 'Yes') {
            // Add relevant metadata to the output template
            $category = str_replace('-', ' ', $postObject->tag);
            if (!in_array($category, $stack)) {
                // If any posts have been created in a list, close the existing <ul> and open a new <ul> after the heading
                if (!empty($stack)) {
                    $metadata .= '</ul>';
                }
                $metadata .= '<h2 id=' . $category . ' class="text-center">' . ucwords($category) . '</h2><ul class="blog-posts">';
                array_push($stack, $category);
            }

            $metadata .= '<li><time datetime=' . date_format(date_create($postObject->created), "Y-m-d") . '>' . date_format(date_create($postObject->created), "Y-m-d") . '</time><a href=' . $postObject->link . ' aria-label="Read ' . $postObject->title . ' blog post">' . $postObject->title . '</a></li>';
        }
    }
    $metadata .= '</ul>';
    return $metadata;
}

function loadPostJSON(string $fileName, string $query)
{
    // Get the file contents and decode JSON
    $fileContents = file_get_contents($fileName);
    $data = json_decode($fileContents);

    // Loop through the JSON object
    foreach ($data as $postObject) {
        // If this function is called to get a specific post's metadata, return the whole object
        if ($postObject->link == $GLOBALS['fullDomain'] . "/post/" . $query) {
            return $postObject;
        }
    }
}

function loadCommentJSON(string $fileName, string $query = null): string
{
    // Set up an empty comment section
    $commentSection = '';

    // Load the file and decode the JSON
    $fileContents = file_get_contents($fileName);
    $data = json_decode($fileContents);

    // Sort comments by date - latest comments will appear at the top
    uasort($data, function ($a, $b) {
        return strcmp($b->timestamp, $a->timestamp);
    });

    // Loop through all comments add to returned string if it matches criteria
    foreach ($data as $commentObject) {
        if (($query !== null) && ($commentObject->postURL == $GLOBALS['fullDomain'] . "/post/" . $query)) {
            $securedHTML = parseMarkdown(null, $commentObject->comment);
            $commentSection .= '<div class="user-comment"><div class="row"><label>Timestamp:</label><p>' . $commentObject->timestamp . '</p></div><div class="row"><label>Name:</label><p>' . $commentObject->username . '</p></div><div class="row markdown"><label>Comment:</label><div class="comment-markdown">' . $securedHTML . '</div></div></div>';
        } else if ($query == null) {
            $securedHTML = parseMarkdown(null, $commentObject->comment);
            $pageURL = str_replace($GLOBALS['fullDomain'] . '/post/', '', $commentObject->postURL);
            $postObject = loadPostJSON('_data/metadata.json', $pageURL);
            $commentSection .= '<div class="user-comment"><div class="row"><label>Post:</label><a href="' . $commentObject->postURL . '#comments">' . $postObject->title . '</a></div><div class="row"><label>Timestamp:</label><p>' . $commentObject->timestamp . '</p></div><div class="row"><label>Name:</label><p>' . $commentObject->username . '</p></div><div class="row markdown"><label>Comment:</label><div class="comment-markdown">' . $securedHTML . '</div></div></div>';
        }
    }
    return $commentSection;
}