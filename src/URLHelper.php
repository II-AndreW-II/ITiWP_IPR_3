<?php

namespace App;

class URLHelper
{
    public function parseQueryString(string $url): array
    {
        $params = [];
        
        $parsedUrl = parse_url($url);
        
        if (!isset($parsedUrl['query'])) {
            return $params;
        }
        
        parse_str($parsedUrl['query'], $params);
        
        return $params;
    }
    
    public function buildQueryString(array $params): string
    {
        if (empty($params)) {
            return '';
        }
        
        return http_build_query($params);
    }
}

