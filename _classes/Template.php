<?php


class Template
{
    public function __construct(string $canonicalURL, string $pageDescription, string $pageTitle, string $pageExtras, string $contentCol, string $commentSection)
    {
        $this->canonicalURL = $canonicalURL;
        $this->description = $pageDescription;
        $this->title = $pageTitle;
        $this->extras = $pageExtras;
        $this->content = $contentCol;
        $this->comments = $commentSection;
        $this->currentYear = date("Y");
    }

    public function echoTemplate()
    {
        // Get the template file
        $templateFile = '_templates/template.html';
        $page = file_get_contents($templateFile);

        // Replace the template variables
        $page = str_replace('{Page_Title}', $this->title, $page);
        $page = str_replace('{Page_Description}', $this->description, $page);
        $page = str_replace('{Canonical_URL}', $this->canonicalURL, $page);
        $page = str_replace('{Page_Extras}', $this->extras, $page);
        $page = str_replace('{Content_Column}', $this->content, $page);
        $page = str_replace('{User_Comments}', $this->comments, $page);
        $page = str_replace('{Current_Year}', $this->currentYear, $page);

        // Echo the filled-out template
        echo $page;
    }
}