<?php

class UtilitiesTest extends PHPUnit_Framework_TestCase
{
    public function testCheckDependencies()
    {
        $this->assertArraySubset(\CashWay\checkDependencies(), []);
    }

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

    /**
     * @dataProvider datesProvider
    */
    public function testGetLocalizedDateInfo($date, $locale, $expected)
    {
        $this->assertSame($expected, \CashWay\getLocalizedDateInfo($date, $locale));
    }

    public function datesProvider()
    {
        return [
            ['2015-01-01T01:01:01Z', 'fr', 'jeudi 1er janvier à 1 heures'],
            ['2016-03-01T15:09:01Z', 'fr', 'mardi 1er mars à 15 heures'],
            ['2016-03-01T15:09:01Z', 'ru', 'mardi 1er mars à 15 heures'],
            [null, 'fr', null]
        ];
    }

    public function testGetRandomString()
    {
        $this->assertSame(48, strlen(\CashWay\getRandomString(24)));
    }
}
