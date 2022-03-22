<?php

function loadRSS(string $fileName): string
{
    // Get the file contents and decode JSON
    $fileContents = file_get_contents($fileName);
    $data = json_decode($fileContents);

    // Set-up RSS variables
    $rssCounter = 0;
    $rssContents = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
    ><channel><atom:link href="' . $GLOBALS['fullDomain'] . '/rss.xml" rel="self" type="application/rss+xml" /><title>Blog</title><link>' . $GLOBALS['fullDomain'] . '</link><description>Lorem ipsum dolor sit amet...</description><copyright>Copyright 20xx - 20xx, My Name</copyright><language>en-us</language><docs>https://cyber.harvard.edu/rss/index.html</docs><lastBuildDate>Mon, 04 Jan 2021 00:00:00 CST</lastBuildDate><ttl>60</ttl><image><url></url><title>Blog</title><link>'.$GLOBALS['fullDomain'].'</link></image>';

    // Loop through the JSON object
    foreach ($data as $postObject) {
        // Only load published posts
        if ($postObject->published == 'Yes') {
            // Parse the markdown to HTML
            include_once('_functions/parseMarkdown.php');
            $fileLink = str_replace($GLOBALS['fullDomain'] . '/post/', '', $postObject->link);
            $fileName = 'posts/' . $postObject->id . '-' . str_replace('.html', '.md', $fileLink);
            $securedHTML = parseMarkdown($fileName);

            if ($rssCounter == 0) {
                $rssContents .= '<lastBuildDate>' . date_format(date_create($postObject->created), 'D, d M Y H:i:s T') . '</lastBuildDate>';
                $rssCounter = $rssCounter + 1;
            }

            $rssContents .=
                '<item><title>' .
                str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $postObject->title) .
                '</title><author>' .
                str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $postObject->author) .
                '</author><dc:creator>' .
                str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $postObject->author) .
                '</dc:creator><link>' .
                str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $postObject->link) .
                '</link><pubDate>' .
                date_format(date_create($postObject->created), 'D, d M Y H:i:s T') .
                '</pubDate><guid>' .
                str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $postObject->link) .
                '</guid><description><![CDATA[' .
                $postObject->description .
                ']]></description><content:encoded><![CDATA[' .
                $securedHTML .
                ']]></content:encoded></item>';
        }
    }
    $rssContents .= '</channel></rss>';
    return $rssContents;
}