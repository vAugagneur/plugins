<?php

class HTTPTest extends PHPUnit_Framework_TestCase
{
    public function testHttpGet()
    {
        $api = new \CashWay\API(get_conf());
        $res = $api->httpGet('/path/to/test', array('query' => 'value'));
        $this->assertEquals('GET', $res['method']);
        $this->assertEquals('/1/path/to/test?query=value', $res['request']);
        $this->assertJsonStringEqualsJsonString(json_encode(null),
            $res['body']);
    }

    public function testHttpPost()
    {
        $api = new \CashWay\API(get_conf());
        $res1 = $api->httpPost('/path/to/test', json_encode(array('query' => 'value')));
        $res = $api->httpPost('/path/to/test', array('query' => 'value'));
        $this->assertEquals($res1, $res);
        $this->assertEquals('POST', $res['method']);
        $this->assertEquals('/1/path/to/test', $res['request']);
        $this->assertJsonStringEqualsJsonString(json_encode(array('query' => 'value')),
            $res['body']);
    }

    public function testHttpDo()
    {
        $api = new \CashWay\API(array('API_URL' => 'http://nowhere'));
        $res = $api->httpDo('GET', '/path/to/test', array('query' => 'value'));
        $this->assertEquals('curl_error', $res['errors'][0]['code']);

        $api = new \CashWay\API(get_conf());
        $res = $api->httpDo('HEAD', '/path/to/test', array('query' => 'value'));
        $this->assertEquals('method_not_supported', $res['errors'][0]['code']);

        $res = $api->httpDo('GET', '/path/to/test', array('query' => 'value'));
        $this->assertEquals('GET', $res['method']);
        $this->assertEquals('/1/path/to/test?query=value', $res['request']);
        $this->assertJsonStringEqualsJsonString(json_encode(null),
            $res['body']);
    }

    public function testCurlDo()
    {
        $res = \CashWay\cURL::curlDo(get_conf()['API_URL'], array(

        ));
        $tr = json_decode($res['body'], true);
        $this->assertEquals('GET', $tr['method']);
        $this->assertEquals('/', $tr['request']);
    }
}
