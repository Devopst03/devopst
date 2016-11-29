<?php

$product = $this->build->product();
?>
<h2>
	Product <?= linkify($product ? $product->link() : NULL, h($product ? $product->name : $this->build->product)) ?>,
	branch <?= git_linkify($this->build->git_url, $this->build->branch, $this->build->branch) ?>.
</h2>
<?php

if ($this->build->success() === NULL) {
	?><h2>In-progress build</h2><?
} elseif (!$this->build->success()) {
	?><h2>BUILD FAILED</h2><?
} else {
    ?><h2>Build complete</h2>

    <?php if ($this->build->product == 'yoko') { ?>
        <p>To deploy just Yoko:</p>
        <p><input style="height: 1.5em; font-size: 1.5em;" onclick="this.select();" size="80" value="talos deploy <?= $this->build->product ?> -b <?= $this->build->build_name ?> --service apache -E ENV -u USERNAME"></p>
    <?php } ?>

    <p>To deploy everything:</p>
    <p><input style="height: 1.5em; font-size: 1.5em;" onclick="this.select();" size="80" value="talos deploy <?= $this->build->product ?> -b <?= $this->build->build_name ?> -E ENV -u USERNAME"></p>

<?php
}

foreach ($this->build as $k => $v) {
	if ($k == 'build_log') continue;
	if (!trim($v)) continue;
	switch ($k) {
		case 'log_file_path':
			$v = '<a href="/log/' . h(basename($v)) . '">' . h($v) . '</a>';
			break;
		default:
			$v = h($v);
	}

	?><li><b><?= h($k) ?></b>: <?= $v ?></li><?
}

function last_20($s) {
	$lines = explode("\n", $s);
	return implode("\n", array_slice($lines, -20));
}

if ($this->build->build_log) {

	$build_log_tail = last_20($this->build->build_log);

	if ($build_log_tail != $this->build->build_log) {
		?>
		<h2>Build log (last 20 lines)</h2>

		<pre><?= h($build_log_tail) ?></pre>
		<?
	}

	?>
	<h2>Full build log</h2>

	<pre><?= h($this->build->build_log) ?></pre>
	<?

}
