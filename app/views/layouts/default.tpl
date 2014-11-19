<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8" />
<title><?= \GGS\Components\WebApplication::$name ?> <?= isset($pageTitle)? '| ' . $pageTitle : ''; ?></title>
<meta name="description" content="<?= \GGS\Components\WebApplication::$name ?>"/>
<link rel="stylesheet" type="text/css" media="all" href="<?= \GGS\Components\WebApplication::$request->getBaseUrlWithoutScript(); ?>/media/css/reset.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?= \GGS\Components\WebApplication::$request->getBaseUrlWithoutScript(); ?>/media/css/style.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
</head>
<body>
<div id="header">
    <div id="row">
        <a class="menu-item" href="<?= \GGS\Components\WebApplication::$request->createUrl('post', 'list'); ?>">
            Home
        </a>
         &nbsp;|&nbsp;
        <a class="menu-item" href="<?= \GGS\Components\WebApplication::$request->createUrl('post', 'create'); ?>">
            Add Post
        </a>
    </div>
</div>
<div id='content'>
    <?= $content ?>
</div>
</body>
</html>