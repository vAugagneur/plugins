<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_0_6($module)
{
	$module->installDefaultValues();

	if (self::isConfiguredService())
	{
		$cashway = self::getCashWayAPI();
		$cashway->updateAccount(array(
			'notification_url' => $module->context->link->getModuleLink('cashway', 'notification'),
			'shared_secret' => Configuration::get('CASHWAY_SHARED_SECRET')
		));
	}

	return true;
}
