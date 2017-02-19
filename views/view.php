<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <link href="main.css" rel="stylesheet" />
    <title>Alaska - Home</title>
</head>
<body>
<header>
    <h1>Alaska</h1>
</header>
<?php foreach ($articles as $article): ?>
    <article>
        <h2><?php echo $article->getTitle() ?></h2>
        <p><?php echo $article->getContent() ?></p>
    </article>
<?php endforeach ?>
<footer class="footer">

</footer>
</body>
</html>