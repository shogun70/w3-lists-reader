<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=koi8-r">
	<title><?= $message['subject'] ?> from <?= $message['author'] ?> on <?= date_format($message['date'], 'Y-m-d') ?> (<?= $list['name'] ?>@w3.org)</title>
	<meta name="Author" content="<?= $message['author'] ?>">
	<meta name="Subject" content="<?= $message['subject'] ?>">
	<meta name="Date" content="<?= date_format($message['date'], 'Y-m-d') ?>">
	<link rel="stylesheet" href="/StyleSheets/Mail/public-message.css" type="text/css">
<body>
<nav title="Navigation bar to upper levels" class="upper">
<p>
   <a href="//www.w3.org/">W3C home</a> &gt;
   <a href="../../" title="Public mailing lists">Public Mailing lists</a> &gt;
   <a href="../" title="Index of <?= $list['name'] ?>@w3.org" rel="start"><?= $list['name'] ?>@w3.org</a> &gt;
   <a href="./" rel="contents" title="Messages received in <?= $period['label'] ?>"><?= $period['label'] ?></a>
</p>
</nav>

<header>
<h1><?= $message['subject'] ?></h1>
</header>
<!-- body="start" -->
<main class="mail">

<address id="headers">
<span id="subject"><dfn>Subject:</dfn> <?= $message['subject'] ?></span><br />
<span id="from"><dfn>From:</dfn> <?= $message['author'] ?></span><br />
<span id="date"><dfn>Date:</dfn> <time datetime="<?= date_format($message['date'], 'Y-m-d') ?>"><?= date_format($message['date'], 'l, j F Y') ?></span><br />
<?php if ($message['in_reply_to']): ?>
<?php $in_reply_to = $message['in_reply_to']; ?>
<span id="in_reply_to"><dfn>In-Reply-To:</dfn> 
<a href="<?= $in_reply_to['path'] ?>" title="Message this replies to"><?= $in_reply_to['author'] ?>: "<span><?= $in_reply_to['subject'] ?></span>" (<time datetime="<?= $in_reply_to['date'] ?>"><?= $in_reply_to['date'] ?></time>)</a>
</span><br />
<?php endif; ?>
</address>

<pre id="body">
<?= $message['content'] ?>
</pre>

</main>

<nav id="replies">

<?php if ($replies): ?>
<dfn>Replies:</dfn>
<ul>

<?php foreach ($replies as $reply): ?>

<li><a href="<?= $reply['path'] ?>" title="Message sent in reply to this message"><?= $reply['author'] ?>: "<span><?= $reply['subject'] ?></span>" (<time datetime="<?= $reply['date'] ?>"><?= $reply['date'] ?></time>)</a></li>

<?php endforeach; ?>
</ul>

<?php endif; ?>

</nav>

<!-- trailer="footer" -->
<footer>
<p>
<?php $canonical_url = "https://lists.w3.org" . $message['path']; ?>
The original source for this page is <a rel="canonical" href="<?= $canonical_url ?>"><?= $canonical_url ?></a>.
</p>
</footer>

</body>
</html>



