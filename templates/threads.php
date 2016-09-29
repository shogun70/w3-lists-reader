<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $list['name'] ?> by thread</title>
<meta name="Subject" content="by thread" />
<meta name="robots" content="noindex" />
<link rel="stylesheet" href="/StyleSheets/Mail/public-messagelist.css" type="text/css" />
</head>
<body>
<nav title="Navigation bar to upper levels" class="upper">
<p>
   <a href="//www.w3.org/">W3C home</a> &gt;
   <a href="../../" title="Public Mailing lists">Public Mailing lists</a> &gt;
   <a href="../" title="Index of <?= $list['name'] ?>" rel="start"><?= $list['name'] ?></a> &gt;
   <a href="./" title="Messages received in <?= $period['label'] ?>"><?= $period['label'] ?></a>
</p>
</nav>

<header>
<h1><?= $list['name'] ?>@w3.org from <?= $period['label'] ?> by new threads</h1>

</header>

<?php if ($newer): ?>
<nav class="next newer">
	<a rel="prev" href="<?= $newer['path'] ?>">Newer threads</a>
</nav>
<?php endif; ?>

<main class="messages-list">
<ul>
<?php foreach ($messages as $message): ?>
<li>
	<a href="<?= $message['path'] ?>">
		<span class="subject"><?= htmlspecialchars($message['subject'], ENT_QUOTES) ?></span>
	</a>&nbsp;
	<em class="author"><?= htmlspecialchars($message['author'], ENT_QUOTES) ?></em>&nbsp;
	<em>(<time datetime="<?= date_format($message['date'], 'Y-m-d') ?>"><?= date_format($message['date'], 'l, d F Y') ?></time>)</em>
	<?php if ($message['no_replies']): ?>
	<strong title="No replies" class="no_replies">*</strong>
	<?php endif; ?>

</li>
<?php endforeach; ?>
</ul>
</main>

<?php if ($older): ?>
<nav class="next older">
	<a rel="next" href="<?= $older['path'] ?>">Older threads</a>
</nav>
<?php endif; ?>

</body>
</html>


