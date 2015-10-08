<?php

class UtilitiesTest extends PHPUnit_Framework_TestCase
{
    public function testVersions()
    {
        $this->assertTrue(\CashWay\isPHPVersionSupported());
    }

    public function testLogInfo()
    {
        ob_start();
        \CashWay\Log::info('Coucou');
        $output = ob_get_clean();
        $this->assertStringMatchesFormat('[%s] INFO: Coucou', $output);
    }

    public function testLogWarn()
    {
        ob_start();
        \CashWay\Log::warn('Coucou');
        $output = ob_get_clean();
        $this->assertStringMatchesFormat('[%s] WARNING: Coucou', $output);
    }

    public function testLogError()
    {
        ob_start();
        \CashWay\Log::error('Coucou');
        $output = ob_get_clean();
        $this->assertStringMatchesFormat('[%s] ERROR: Coucou', $output);
    }
}
