<!DOCTYPE html>
<meta charset="utf-8" />
<link rel="frameset" href="/frameset.html" />
<script>
(function() {
	if (!(/https?:/.test(location.protocol) && window.XMLHttpRequest && history.pushState && window.MutationObserver)) return;
	var script = document.createElement('script');
	script.src = "/boot.js";
	document.write(script.outerHTML);
})();
</script>
<!-- original content below -->

<?= $contents ?>

