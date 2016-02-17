<?php

return array(
  'extension_name'         => 'CashWay',
  'summary'                => 'E-payment cash solution',
  'description'            => 'Payment method with possibility to buy with cash money.',
  'notes'                  => 'Typo fixes, properly aborts ajax requests.',
  'extension_version'      => '0.1.0',
  'skip_version_compare'   => false,
  'auto_detect_version'    => true,

  'stability'              => 'stable',
  'license'                => 'General Public License (GPL)',
  'channel'                => 'community',

  'author_name'            => 'Kassim Belghait',
  'author_user'            => 'Sirateck',
  'author_email'           => 'kassim@sirateck.com',

  'base_dir'               => __DIR__.'/build',
  'archive_files'          => 'Sirateck_Cashway.tar',
  'path_output'            => __DIR__.'/build',

  'php_min'                => '5.2.0',
  'php_max'                => '6.0.0',

  'extensions'             => array()
);
