
<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8" />
<title><?= \GGS\Components\Application::$name ?> <?= isset($pageTitle)? '| ' . $pageTitle : ''; ?></title>
<meta name="description" content="<?= \GGS\Components\Application::$name ?>"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
</head>
<body>
<div id="header">
    <div id="logo">
        <a href="<?= \GGS\Components\Controller::createUrl('post', 'list'); ?>">
            Home
        </a>
         &nbsp;|&nbsp;
        <a href="<?= \GGS\Components\Controller::createUrl('post', 'create'); ?>">
            Add Post
        </a>
    </div>
</div>
<div id='content'>
    <?= $content ?>
</div>
</body>
</html>