<?php

// Set global variables for this specific website
// Domains must match the URLs in the _data files
$websiteProtocol = 'https://';
$shortDomain = 'blog.example.com';
$fullDomain = $websiteProtocol . $shortDomain;

// Trim the leading slashes & split the path on slashes
$path = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $path);

// Show home if there are no parameters or paths
if (empty($elements[0])) {
    showHomepage();
} // Else, identify the first item in the URL so we can identify which function to use
else {
    switch (array_shift($elements)) {
        case 'post':
            showPost($elements);
            break;
        case 'submit-comment':
            include_once('_functions/submitComment.php');
            submitComment();
            break;
        case 'comments':
            showComments();
            break;
        case 'category':
            header('Location: ' . $fullDomain . '/categories/');
            die();
        case 'categories':
            showCategory();
            break;
        case 'rss':
        case 'rss.xml':
            showRSS();
            break;
        case 'robots.txt':
            showRobots();
            break;
        default:
            header('HTTP/1.1 404 Not Found');
    }
}

function showHomepage()
{
    // Add Articles
    $contentCol = '<h1>My Blog</h1>';

    // Get metadata on all posts from the metadata.json file
    include_once('_functions/loadJSON.php');
    $contentCol .= loadHomepageJSON('_data/metadata.json');

    // Create a template
    include_once('_classes/Template.php');
    $template = new Template(
        $GLOBALS['fullDomain'],
        'Explore the thoughts of...',
        'Blog | YourName',
        '',
        $contentCol,
        ''
    );

    // Echo the HTML to the user
    $template->echoTemplate();
}

function showPost($params)
{
    // URL Parameter
    $query = $params[0];

    // Get metadata on this post from the metadata.json file
    include_once('_functions/loadJSON.php');
    $headerData = loadPostJSON('_data/metadata.json', $query);

    // Apply metadata to post header
    $header = '<header><h1 class="blog-post-title">' . $headerData->title . '</h1><div class="blog-post-metadata"><time class="d-inline" datetime="' . date_format(date_create($headerData->created), "Y-m-d") . '">' . date_format(date_create($headerData->created), "Y-m-d") . '</time><span style="margin:0 0.5rem;">::</span><a href="' . $GLOBALS['fullDomain'] . '/categories/#' . $headerData->tag . '">#' . $headerData->tag . '</a></div></header>';

    // Get post .md file that matches the .html file requested in the URL
    include_once('_functions/parseMarkdown.php');
    $fileName = './posts/' . $headerData->id . '-' . str_replace('.html', '.md', $query);
    $securedHTML = parseMarkdown($fileName);

    // Echo Results
    $contentCol = '<article class="blog-post">' . $header . '<section class="blog-post-body">' . $securedHTML . '</section></article>';

    // Create a comment section
    $postURL = $GLOBALS['fullDomain'] . '/post/' . $query;
    $commentForm = '<form action="/submit-comment/" method="POST"><h3>Leave a Comment</h3><section hidden><label class="form-label" for="postURL">Post URL</label><input class="form-control" id="postURL" name="postURL" type="text" value="' . $postURL . '"></section><section><label class="form-label" for="userName">Display Name</label><input class="form-control" id="userName" name="userName" placeholder="John Doe" type="text"></section><section><label class="form-label" for="userContent">Your Comment</label><textarea class="form-control" id="userContent" name="userContent" rows="3" placeholder="# Feel free to use Markdown" aria-describedby="commentHelp" required></textarea><div id="commentHelp" class="form-text">Comments are saved as Markdown and cannot be edited or deleted.</div></section><button type="submit">Submit</button></form>';

    // Load saved comments
    $userComments = '<section id="comments" class="comments"><h3>Comments</h3>';
    $userComments .= loadCommentJSON('_data/comments.json', $query);
    $userComments .= '</section>';

    // Combine comment form and previous user comments
    $commentSection = $commentForm . $userComments;

    // Create a template
    include_once('_classes/Template.php');
    $template = new Template(
        $GLOBALS['fullDomain'] . '/post/' . $query,
        $headerData->description . ' Read more at ' . $GLOBALS['fullDomain'] . '!',
        $headerData->title . ' | Blog',
        '<link rel="stylesheet" href="/static/prism.css"><script src="/static/prism.js"></script>',
        $contentCol,
        $commentSection
    );

    // Echo the HTML to the user
    $template->echoTemplate();
}

function showComments()
{
    // Add title
    $commentHeader = '<h1>Recent Comments</h1>';

    // Load saved comments
    include_once('_functions/parseMarkdown.php');
    include_once('_functions/loadJSON.php');
    $userComments = '<section id="comments" class="comments"><h3>Comments Across All Blog Posts</h3>';
    $userComments .= loadCommentJSON('_data/comments.json');
    $userComments .= '</section>';

    // Combine comment form and previous user comments
    $commentSection = $commentHeader . $userComments;

    // Create a template
    include_once('_classes/Template.php');
    $template = new Template(
        $GLOBALS['fullDomain'] . '/comments/',
        'Read through some recent comments for blog posts at ' . $GLOBALS['fullDomain'] . '.',
        'Recent Comments | Blog',
        '<link rel="stylesheet" href="/static/prism.css"><script src="/static/prism.js"></script><meta name="robots" content="noindex,nofollow">',
        '',
        $commentSection
    );

    // Echo the HTML to the user
    $template->echoTemplate();
}

function showCategory()
{
    // Open the article list
    $contentCol = '<h1>Categories</h1>';

    // Get metadata on all posts from the metadata.json file
    include_once('_functions/loadJSON.php');
    $contentCol .= loadCategoryJSON('_data/metadata.json');

    // Create a template
    include_once('_classes/Template.php');
    $template = new Template(
        $GLOBALS['fullDomain'] . '/categories/',
        'Browse the categories for blog posts at ' . $GLOBALS['fullDomain'] . '.',
        'Categories | Blog',
        '<meta name="robots" content="noindex,nofollow">',
        $contentCol,
        ''
    );

    // Echo the HTML to the user
    $template->echoTemplate();
}

function showRSS()
{
    // Loop through the metadata file and display any article that is published
    include_once('_functions/loadRSS.php');
    $rssContents = loadRSS('_data/metadata.json');

    // Echo the RSS XML
    header('Content-type: text/xml');
    echo $rssContents;
    die();
}

function showRobots()
{
    header('Content-type: text/plain');
    echo 'User-agent: * Disallow:';
    die();
}
