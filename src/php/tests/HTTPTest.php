<?php

class HTTPTest extends PHPUnit_Framework_TestCase
{
    public function testHttpGet()
    {
        $payload = ['query' => 'value'];

        $api = new \CashWay\API(get_conf());
        $res = $api->httpGet('/path/to/test', $payload);
        $this->assertEquals('GET', $res['method']);
        $this->assertEquals('/1/path/to/test?query=value', $res['request']);
        //$this->assertJsonStringEqualsJsonString(json_encode($payload), $res['body']);
        $this->assertEquals('200', $api->last_http_code);
    }

    public function testHttpPost()
    {
        $payload = ['query' => 'value'];

        $api = new \CashWay\API(get_conf());
        $res = $api->httpPost('/path/to/test', $payload);
        $this->assertEquals('POST', $res['method']);
        $this->assertEquals('/1/path/to/test', $res['request']);
        $this->assertJsonStringEqualsJsonString(json_encode($payload), $res['body']);
        $this->assertEquals('200', $api->last_http_code);
    }

    public function testHttpDo()
    {
        $payload = ['query' => 'value'];

        $api = new \CashWay\API(array('API_URL' => 'http://127.0.0.1:81'));
        $res = $api->httpDo('GET', '/path/to/test', $payload);
        $this->assertEquals('curl_error', $res['errors'][0]['code']);

        $api = new \CashWay\API(get_conf());
        $res = $api->httpDo('HEAD', '/path/to/test', $payload);
        $this->assertEquals('method_not_supported', $res['errors'][0]['code']);

        $res = $api->httpDo('GET', '/path/to/test', $payload);
        $this->assertEquals('GET', $res['method']);
        $this->assertEquals('/1/path/to/test?query=value', $res['request']);
        $this->assertEquals('', $res['body']);
    }

    public function testCurlDo()
    {
        $res = \CashWay\cURL::curlDo(get_conf()['API_URL'], array(

        ));
        $tr = json_decode($res['body'], true);
        $this->assertEquals('GET', $tr['method']);
        $this->assertEquals('/', $tr['request']);
        $this->assertEquals('200', $res['code']);
    }
}
