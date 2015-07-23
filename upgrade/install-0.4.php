<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_0_4($module)
{
	$module->registerHook('actionOrderStatusUpdate');
	return true;
}
