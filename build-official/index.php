<?php

$files = array_reverse(glob(__DIR__ . '/*.zip'));
$f_tmpl = '<li><a href="%s" class="btn">Télécharger la version %s</a><br>%s %s</li>';
$s = '';

foreach ($files as $f)
{
    $f = basename($f);
    $hash = '';
    $signed = '';
    $name = '';

    $tmp = explode('-', $f);
    $name = 'version ' . str_replace('.zip', '', $tmp[2]);

    if (file_exists($f . '.sha256')) {
        $hash = sprintf('<a href="%s" class="label">hash sha256</a>', $f . '.sha256');
    }
    if (file_exists($f . '.sha256.asc')) {
        $signed = sprintf('<a href="%s" class="label">signature sha256.asc</a>', $f . '.sha256.asc');
    }

    $s .= sprintf($f_tmpl, $f, $name, $hash, $signed);

}

?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>CashWay - Modules installables pour les boutiques</title>
  <meta name="keywords" content="cashway, prestashop, magento, woocommerce, php, api">
  <link href="https://www.cashway.fr/releases/" rel="canonical">
  <link href="https://www.cashway.fr/wp-content/uploads/2015/06/fav.png" type="image/png" rel="icon">
  <link href="https://www.cashway.fr/wp-content/uploads/2015/06/fav.png" type="image/png" rel="shortcut icon">
  <link href="https://fonts.googleapis.com/css?family=Advent+Pro:400,500,600,700" rel="stylesheet" type="text/css">
  <style>
  html, body { margin: 0; padding: 0; text-align: center; }
  body {
    font: 16px/27px Roboto, Helvetica, Arial, Verdana, sans-serif;
    word-spacing: normal;
  }
  header { width: 100%; margin: auto; }
  article, footer {
    width: 900px;
    margin: 0 auto;
  }
  section {
    border: 1px solid #FF8F02;
    width: 50%;
    margin: 1rem auto;
  }
  h1, h2, h3 {
    font: bold 22px/24px "Advent Pro",Helvetica,Arial,Verdana,sans-serif;
    color: #FF8F02;
    text-transform: none;
  }
  h2 { font-size: 18px; }
  h3 { font-size: 16px; }
  h1 span, h2 span { display: block; font-size: smaller; font-weight: normal; }
  .label { background: #ddd; font-size: 76%; }
  a.label  { color: black; text-decoration: none; padding: 0 0.15rem; font-family: "Andale mono"; }

  .cw-header {}
  .cw-topnav { background: black; color: white; font-size: 13px; }
  .cw-topnav p { margin: 0; padding: 0; }
  #logo { padding: 30px 0 0 0; }
  ul { margin: 0; padding: 0; }
  li { list-style: none; }

  @media (max-width: 900px) {
    article, footer { width: 100%; }
    section { width: 90%; }
  }
  </style>
</head>
<body>
  <header class="cw-header">
    <div class="cw-topnav">
      <p>CashWay&nbsp;: payez en espèces sur Internet</p>
    </div>
    <a href="https://www.cashway.fr/"><img src="https://www.cashway.fr/wp-content/uploads/2015/06/logo_v2.png"
                                           alt="CashWay"
                                           id="logo"
                                           width="140"
                                           height="115"></a>

      <h1>Modules installables<br>
        pour les boutiques</h1>
  </header>
  <article>

    <section>
      <h2>Pour PrestaShop
        <span>(versions 1.6 et supérieures)</span></h2>
      <ul>
          <?php echo $s ?>
      </ul>
    </section>

    <section>
      <h2>Pour Magento, WooCommerce</h2>
      <em>En cours</em>
    </section>

    <section>
      <h2>Pour développeurs</h2>
      <ul>
        <li><a href="https://help.cashway.fr/shops/">Documentation de l'API</a></li>
        <li>SDK <a href="https://github.com/cshw/plugins/src/php">PHP</a></li>
      </ul>
    </section>
  </article>
  <footer>
  </footer>
</body>
</html>

