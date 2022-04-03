# PHP-Blog

PHP-Blog is a simple, database-free blogging web app written in PHP, with the
support of Markdown and JSON.

## Philosophy

Websites, blogs included, should be able to
[degrade gracefully over time](https://brandur.org/fragments/graceful-degradation-time).
For blogs, this means open-sourcing your blog and publishing articles and
metadata straight to the Git repository.

This project allows you to blog and keep your articles, article metadata, and
user comments within this repo. This is done through the use of JSON files to
store article metadata and user comments (instead of using a database), and the
use of Markdown files to store written articles.

## Installation

Steps:

```bash
cd yourBlog.com
```

```bash
git clone https://github.com/christian-cleberg/php-blog
```

Change the global variables at the top of the `index.php` script:

```php
$websiteProtocol = 'https://';
$shortDomain = 'yourBlog.com';
```

Remove my personal data files:

```bash
cd _data/ && rm -rf *
```

Add blank data files:

```bash
touch comments.json && touch metadata.json
```

Remove my personal posts:

```bash
cd ../posts/ && rm -rf *
```

Add your first post:

```bash
touch 001-your-first-post.md
```

For every post you add, also add the applicable JSON object in
`_data/metadata.json`.

Finally, enable `FallbackResource` in your Apache `.conf` file or in your
`.htaccess` file:

```apacheconf
FallbackResource /index.php
```

## Project Structure

This project uses the structure show below. Continue reading for a brief
description of each file/folder.

```text
blog/
│─── .gitignore
│─── favicon.ico
│─── index.php
│─── LICENSE
│─── README.md
│
└─── _classes/
│   │─── Comment.php
│   │─── Parsedown.php
│   └─── Template.php
│
└─── _data/
│   │─── comments.json
│   └─── metadata.json
│
└─── _functions/
│   │─── loadJSON.php
│   │─── loadRSS.php
│   │─── parseMarkdown.php
│   └─── submitComment.php
│
└─── _templates/
│   └───template.html
│
└─── posts/
│   │─── 001-your-first-post.md
│   │─── ...
│   └─── 999-many-posts-later.md
│
└─── static/
    │─── app.css
    │─── prism.css
    └─── prism.js
```

### File: `index.php`

This file is the FallbackResource for the entire directory, so non-existent
folders/files requested by users will be sent to `index.php` for processing.
This file uses a switch statement to read the request URL and direct the user to
various functions.

Each function in this file will call various other functions/classes and then
send the output to the template file.

---

### Folder: `_classes`

#### File: `Comment.php`

This class allows for the creation of user comments and provides the
`saveComment()` function to do so.

#### File: `Parsedown.php`

This class was not created by me, it comes from
[erusev/parsedown](https://github.com/erusev/parsedown). See his
[MIT License](https://raw.githubusercontent.com/erusev/parsedown/master/LICENSE.txt)
for licensing questions on this code.

This class provides numerous capabilities to parse Markdown files and variables
to HTML.

#### File: `Template.php`

This class allows for the creation of a final HTML page to show the user. The
`echoTemplate()` function will replace the `{}` templates in the
`_templates/template.html` file.

---

### Folder: `_data`

#### File: `metadata.json`

Your `metadata.json` file will need to contain an object for each post in the
`post` folder. If you don't add an object for your new blog post, it won't be
displayed in the browser. The structure will follow this exactly:

```
{
  "id": "001",
  "title": "Title of Post",
  "author": "Your Name",
  "description": "A quick little description for SEO and RSS.",
  "tag": "my-category",
  "created": "2018-12-08 00:00:00",
  "modified": "2018-12-08 00:00:00",
  "link": "https:\/\/www.example.com\/post\/blog-post-name.html",
  "published": "Yes"
}
```

#### File: `comments.json`

This file is updated server-side as users submit comments on posts. You can
commit these changes back to your repository by making sure the server-side git
repo can read & write. Then just create git commits back to your repo from there
(you can even create periodic cron jobs to do this for you, if you trust it).

You will need to perform the following commands to ensure whatever 'user' is
writing to the `comments.json` file has the proper permissions. For Apache, the
user is probably `www-data`.

```bash
chgrp -R www-data /path/to/website/
chmod -R g+w _data/comments.json
```

---

### Folder: `_functions`

#### File: `loadJSON.php`

This file contains four (4) functions that can be called to load JSON data from
a local file in various ways.

#### File: `loadRSS.php`

This file contains a single function that will grab the `_data/metadata.json`
file and generate a complete RSS file.

#### File: `parseMarkdown.php`

This file uses the `Parsedown` class to parse a posts or comments from Markdown
to HTML. It also adds additional attributes to any link found in the Markdown.

#### File: `submitComment.php`

This file uses the `Comment` class to create a comment and then save it to
`_data/comments.json`.

---

### Folder: `_templates`

#### File: `template.html`

This file is the end result template that will be shown to users, regardless of
the URL they enter. The main content in the `<body>` and various elements in the
`<head>` will change depending which function calls this template.

---

### Folder: `posts`

Your `posts` folder will contain the `.md` files that will comprise the body of
your blog post. Formatting follows standard Markdown guidelines.

---

### Folder: `static`

This folder contains the main CSS file (`app.css`), as well as a pair of
optional static files from [PrismJS](https://prismjs.com) to enable syntax
highlighting in any code blocks found in posts or comments.

To remove the optional files, simply delete the unwanted files and remove the
following from the `pageExtras` parameter string in the `ShowComments()` and
`ShowPost()` functions in `index.php`:

```html
<link rel="stylesheet" href="/static/prism.css" />
<script src="/static/prism.js"></script>
```
