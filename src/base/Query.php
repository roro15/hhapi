<?php

abstract class Query {

    protected $client;
    protected $modelClass;
    
    protected $baseUrl;
    protected $pathSections = [];
    protected $queryParams = [];

    public function __construct(Client $client, $modelClass) {
        $this->client = $client;
        $this->modelClass = $modelClass;
        $this->setRelativeUrl(call_user_func([$modelClass, 'getUrl']));
    }
    
    public function setRelativeUrl($url) {
        $this->baseUrl = $this->client->getBaseUrl();
        list($sections, $params) = $this->parseRelativeUrl($url);
        $this->pathSections = $sections;
        $this->queryParams = $params;
    }
    
    public function setAbsoluteUrl($url) {
        $info = parse_url($url);
        $this->baseUrl = $info['schema'] . '://' . $info['host'] . '/';
        $this->pathSections = empty($info['path']) ? [] : $this->parseUrlPath($info['path']);
        $this->queryParams = empty($info['query']) ? [] : $this->parseQueryString($info['query']);
    }

    public function addPathSection($section) {
        $this->pathSections[] = $section;
        return $this;
    }
    
    public function removeSections() {
        $this->pathSections = [];
        return $this;
    }
    
    public function setQueryParam($param, $value) {
        $this->queryParams[$param] = $value;
        return $this;        
    }
    
    public function hasQueryParam($param) {
        return isset($this->queryParams[$param]);
    }
    
    public function addQueryParam($param, $value) {
        if (!$this->hasQueryParam($param)) {
            $this->setQueryParam($param, $value);
        }
        return $this;
    }

    public function removeQueryParam($param) {
        unset($this->queryParams[$param]);
        return $this;
    }
    
    public function removeQueryParams() {
        $this->queryParams = [];
        return $this;
    }

    public function setPage($value) {
        return $this->setQueryParam('page', $value);
    }

    public function setPerPage($value) {
        return $this->setQueryParam('per_page', $value);
    }

    public function one() {
        $response = $this->getSearchResponse();
        $class = $this->getModelClass();
        $model = new $class($this->client, $response->getContent());
        return $model;
    }

    public function all() {
        $class = $this->getModelClass();
        $models = [];
        $response = $this->getSearchResponse();
        $pagination = new Pagination($totalCount);
        return $models;
    }

    protected function parseUrlPath($path) {
        return array_filter(explode('/', $path));
    }
    
    protected function parseQueryString($query) {
        $params = [];
        parse_str($query, $params);
        return $params;
    }
    
    protected function parseRelativeUrl($url) {
        $parts = explode('?', $url);
        $path = $parts[0];
        if (count($parts) < 2) {
            $params = [];
        } else {
            $params = $this->parseQueryString($parts[1]);
        }
        $sections = $this->parseUrlPath($path);
        return [$sections, $params];
    }
    
    protected function buildUrl() {
        $url = $this->baseUrl . implode('/', $this->pathSections);
        if (!empty($this->queryParams)) {
            $url .= '?' . http_build_query($this->queryParams);
        }
        return $url;
    }

}
