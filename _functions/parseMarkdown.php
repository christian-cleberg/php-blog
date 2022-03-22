<?php

function parseMarkdown(string $fileName = null, string $fileContents = null)
{
    if ($fileName != null) {
        // Get file contents
        $fileContents = file_get_contents('/var/www/' . $GLOBALS['shortDomain'] . '/' . $fileName, FILE_USE_INCLUDE_PATH);
    }

    // Parse the markdown to HTML
    include_once('_classes/Parsedown.php');
    $md = Parsedown::instance()->text($fileContents);
    $html = new DOMDocument();
    $html->loadHTML($md);
    $html_links = $html->getElementsByTagName('a');
    foreach ($html_links as $html_ink) {
        $html_ink->setAttribute('rel', 'noopener,noreferrer');
        $html_ink->setAttribute('target', '_blank');
    }
    return $html->saveHTML();
}