<?php

namespace hh\base;
use hh\exception\BaseException;
use hh\exception\ResponseException;

abstract class Query {

    protected $client;    
    protected $baseUrl;
    protected $pathSections = array();
    protected $queryParams = array();
    protected $method = 'GET';
    protected $isSecure = true;
    protected $isAsArray = false;
    protected $content;
    protected $multipleResponseAttribute = 'items';
    
    abstract public function getUrl();
    abstract public function getModelClass();
    
    public function __construct(Client $client) {
        $this->client = $client;
        $this->setRelativeUrl($this->getUrl());
    }
    
    public function setRelativeUrl($url) {
        $this->baseUrl = $this->client->getBaseUrl();
        list($sections, $params) = $this->parseRelativeUrl($url);
        $this->pathSections = $sections;
        $this->queryParams = $params;
        return $this;
    }
    
    public function setAbsoluteUrl($url) {
        $info = parse_url($url);
        $this->baseUrl = $info['scheme'] . '://' . $info['host'] . '/';
        $this->pathSections = empty($info['path']) ? array() : $this->parseUrlPath($info['path']);
        $this->queryParams = empty($info['query']) ? array() : $this->parseQueryString($info['query']);
        return $this;
    }

    public function addPathSection($section) {
        $this->pathSections[] = $section;
        return $this;
    }
    
    public function removeSections() {
        $this->pathSections = array();
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
        $this->queryParams = array();
        return $this;
    }
    
    public function setMethod($value) {
        $this->method = $value;
        return $this;
    }
    
    public function getMethod() {
        return $this->method;
    }
    
    public function setContent($value) {
        $this->content = $value;
        return $this;
    }
    
    public function getContent() {
        return $this->content;
    }

    public function setPage($value) {
        return $this->setQueryParam('page', $value);
    }

    public function setPerPage($value) {
        return $this->setQueryParam('per_page', $value);
    }
    
    public function getClient() {
        return $this->client;
    }
    
    public function getMultipleResponseAttribute() {
        return $this->multipleResponseAttribute;
    }
    
    public function setMultipleResponseAttribtue($value) {
        $this->multipleResponseAttribute = $value;
        return $this;
    }
    
    public function raw() {
        $response = $this->buildResponse(); 
        if ($response->getStatusCode() != 200) {
            throw new ResponseException('failed load model', BaseException::ERROR_SERVER, $response);
        }
        return $response->getParsed();
    }

    public function one() {
        return $this->createModel($this->raw());
    }

    public function all($attribute = null) {
        $raw = $this->raw();
        return $this->createModels($raw, $attribute);
     }
   
    public function collection($attribute = null) {
        $raw = $this->raw();
        $models = $this->createModels($raw, $attribute);
        if (!property_exists($raw, 'found')) {
            throw new BaseException('no pagination attributes in response');
        }
        $pagination = new Pagination($raw->found, $raw->per_page, $raw->page);
        return new Collection($pagination, $this, $models);
    }

    protected function parseUrlPath($path) {
        return array_filter(explode('/', $path));
    }
    
    protected function parseQueryString($query) {
        $params = array();
        parse_str($query, $params);
        return $params;
    }
    
    protected function parseRelativeUrl($url) {
        $parts = explode('?', $url);
        $path = $parts[0];
        if (count($parts) < 2) {
            $params = array();
        } else {
            $params = $this->parseQueryString($parts[1]);
        }
        $sections = $this->parseUrlPath($path);
        return array($sections, $params);
    }
    
    protected function buildUrl() {
        $url = $this->baseUrl . implode('/', $this->pathSections);
        if (!empty($this->queryParams)) {
            $url .= '?' . http_build_query($this->queryParams);
        }
        return $url;
    }
    
    protected function buildResponse() {
        $url = $this->buildUrl();
        $requestMethod = $this->isSecure ? 'secureRequest' : 'request';
        return $this->client->$requestMethod($url, $this->getMethod(), array(), $this->getContent());
    }
    
    protected function createModel($raw) {
        $modelClass = $this->getModelClass();
        return new $modelClass($this->getClient(), $raw);
    }
    
    protected function createModels($raw, $attribute) {
        if (is_null($attribute)) {
            $attribute = $this->getMultipleResponseAttribute();
        }
        if (!is_array($raw->$attribute)) {
            throw new BaseException('no attribtue ' . $attribute . ' in response');
        }
        $models = array();
        foreach($raw->$attribute as $itemRaw) {
            $models[] = $this->createModel($itemRaw); 
        }
        return $models;
    }
}
