<?php

$files = glob(__DIR__ . '/*.zip');
$f_tmpl = '<li><a href="%s">%s</a> %s %s</li>';
$s = '';

foreach ($files as $f)
{
    $f = basename($f);
    $hash = '';
    $signed = '';

    if (file_exists($f . '.sha256')) {
        $hash = sprintf('<a href="%s" class="label">sha256</a>', $f . '.sha256');
    }
    if (file_exists($f . '.sha256.asc')) {
        $signed = sprintf('<a href="%s" class="label">sha256.asc</a>', $f . '.sha256.asc');
    }

    $s .= sprintf($f_tmpl, $f, $f, $hash, $signed);

}

?><!DOCTYPE html>
<html>
<head>
  <title>CashWay Shop plugins</title>
  <meta charset="utf-8">
  <style>
  .label { background: #ddd; font-size: 76%; }
  a.label  { color: black; text-decoration: none; padding: 0 0.15rem; font-family: "Andale mono"; }
  </style>
</head>
<body>
    <h1>Plugins pour CashWay</h1>

    <h2>Pour PrestaShop 1.6+</h2>
    <ul>
        <?php echo $s ?>
    </ul>

    <h2>Pour Magento</h2>
    <em>En cours</em>

    <h2>Pour WooCommerce</h2>
    <em>En cours</em>

    <hr>

    <p><a href="https://help.cashway.fr/shops/">Documentation API pour développeurs</a></p>

</body>
</html>
