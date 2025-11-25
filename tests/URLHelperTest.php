<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\URLHelper;

class URLHelperTest extends TestCase
{
    private URLHelper $urlHelper;
    
    protected function setUp(): void
    {
        $this->urlHelper = new URLHelper();
    }
    
    public function testParseQueryStringSimple(): void
    {
        $url = 'https://example.com?name=John&age=30';
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertEquals(['name' => 'John', 'age' => '30'], $result);
    }
    
    public function testParseQueryStringMultipleParams(): void
    {
        $url = 'https://example.com/search?q=test&page=1&limit=10&sort=asc';
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertCount(4, $result);
        $this->assertEquals('test', $result['q']);
        $this->assertEquals('1', $result['page']);
        $this->assertEquals('10', $result['limit']);
        $this->assertEquals('asc', $result['sort']);
    }
    
    public function testParseQueryStringNoQuery(): void
    {
        $url = 'https://example.com/page';
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
    
    public function testParseQueryStringEmptyValues(): void
    {
        $url = 'https://example.com?param1=&param2=value&param3=';
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertCount(3, $result);
        $this->assertEquals('', $result['param1']);
        $this->assertEquals('value', $result['param2']);
        $this->assertEquals('', $result['param3']);
    }
    
    public function testParseQueryStringEncoded(): void
    {
        $url = 'https://example.com?name=John+Doe&email=test%40example.com';
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertEquals('John Doe', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
    }
    
    public function testParseQueryStringArrayValues(): void
    {
        $url = 'https://example.com?tags[]=php&tags[]=unit-test&tags[]=phpunit';
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertIsArray($result['tags']);
        $this->assertCount(3, $result['tags']);
        $this->assertContains('php', $result['tags']);
        $this->assertContains('unit-test', $result['tags']);
        $this->assertContains('phpunit', $result['tags']);
    }
    
    public function testBuildQueryStringSimple(): void
    {
        $params = ['name' => 'John', 'age' => '30'];
        $result = $this->urlHelper->buildQueryString($params);
        
        $this->assertStringContainsString('name=John', $result);
        $this->assertStringContainsString('age=30', $result);
    }
    
    public function testBuildQueryStringEmpty(): void
    {
        $params = [];
        $result = $this->urlHelper->buildQueryString($params);
        
        $this->assertEquals('', $result);
    }
    
    public function testBuildQueryStringMultiple(): void
    {
        $params = [
            'q' => 'test',
            'page' => '1',
            'limit' => '10',
            'sort' => 'asc'
        ];
        $result = $this->urlHelper->buildQueryString($params);
        
        $this->assertStringContainsString('q=test', $result);
        $this->assertStringContainsString('page=1', $result);
        $this->assertStringContainsString('limit=10', $result);
        $this->assertStringContainsString('sort=asc', $result);
    }
    
    public function testBuildQueryStringEncoded(): void
    {
        $params = [
            'name' => 'John Doe',
            'email' => 'test@example.com'
        ];
        $result = $this->urlHelper->buildQueryString($params);
        
        $this->assertStringContainsString('name=John+Doe', $result);
        $this->assertStringContainsString('email=test%40example.com', $result);
    }
    
    public function testBuildQueryStringEmptyValues(): void
    {
        $params = [
            'param1' => '',
            'param2' => 'value',
            'param3' => ''
        ];
        $result = $this->urlHelper->buildQueryString($params);
        
        $this->assertStringContainsString('param1=', $result);
        $this->assertStringContainsString('param2=value', $result);
        $this->assertStringContainsString('param3=', $result);
    }
    
    public function testBuildQueryStringArrayValues(): void
    {
        $params = [
            'tags' => ['php', 'unit-test', 'phpunit']
        ];
        $result = $this->urlHelper->buildQueryString($params);
        
        $this->assertStringContainsString('tags', $result);

        $this->assertStringContainsString('php', $result);
        $this->assertStringContainsString('unit-test', $result);
        $this->assertStringContainsString('phpunit', $result);
    }
    
    public function testRoundTrip(): void
    {
        $originalUrl = 'https://example.com?name=John&age=30&email=test%40example.com';
        $parsed = $this->urlHelper->parseQueryString($originalUrl);
        $built = $this->urlHelper->buildQueryString($parsed);

        $rebuiltUrl = 'https://example.com?' . $built;
        $reparsed = $this->urlHelper->parseQueryString($rebuiltUrl);
        
        $this->assertEquals($parsed['name'], $reparsed['name']);
        $this->assertEquals($parsed['age'], $reparsed['age']);
        $this->assertEquals($parsed['email'], $reparsed['email']);
    }
    
    public function testParseQueryStringWithFragment(): void
    {
        $url = 'https://example.com?page=1#section';
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertEquals(['page' => '1'], $result);
    }
    
    public function testParseQueryStringWithPort(): void
    {
        $url = 'https://example.com:8080?param=value';
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertEquals(['param' => 'value'], $result);
    }
    
    public function buildQueryStringProvider(): array
    {
        return [
            'простой массив' => [
                ['name' => 'John', 'age' => '30'],
                'name=John&age=30'
            ],
            'массив с пустыми значениями' => [
                ['param1' => '', 'param2' => 'value'],
                'param1=&param2=value'
            ],
            'массив с одним элементом' => [
                ['key' => 'value'],
                'key=value'
            ],
            'массив с числовыми значениями' => [
                ['page' => '1', 'limit' => '10'],
                'page=1&limit=10'
            ],
        ];
    }
    
    public function testBuildQueryStringWithDataProvider(array $params, string $expected): void
    {
        $result = $this->urlHelper->buildQueryString($params);
        
        $expectedParts = explode('&', $expected);
        foreach ($expectedParts as $part) {
            $this->assertStringContainsString($part, $result);
        }
    }
    
    public function parseQueryStringProvider(): array
    {
        return [
            'простой URL' => [
                'https://example.com?name=John&age=30',
                ['name' => 'John', 'age' => '30']
            ],
            'URL без query-строки' => [
                'https://example.com/page',
                []
            ],
            'URL с одним параметром' => [
                'https://example.com?key=value',
                ['key' => 'value']
            ],
            'URL с пустыми значениями' => [
                'https://example.com?param1=&param2=value',
                ['param1' => '', 'param2' => 'value']
            ],
        ];
    }
    
    public function testParseQueryStringWithDataProvider(string $url, array $expected): void
    {
        $result = $this->urlHelper->parseQueryString($url);
        
        $this->assertEquals($expected, $result);
    }
}

