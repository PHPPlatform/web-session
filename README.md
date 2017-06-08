# PHP Platforms Web Session Implementation
This packages implements the Session interface from [php-platform/session](https://github.com/PHPPlatform/session) package for web 

[![build status](https://gitlab.com/php-platform/web-session/badges/master/build.svg)](https://gitlab.com/php-platform/web-session/commits/master) [![coverage report](https://gitlab.com/php-platform/web-session/badges/master/coverage.svg)](https://gitlab.com/php-platform/web-session/commits/master)


## Introduction

A session can be created for a web application or can be for command lines 
This package implements the Session for web

## Usage

confugure ``PhpPlatform\WebSession\Session`` as the `session.class` in [php-platform/session](https://github.com/PHPPlatform/session) `config.json`

```json
{
    "session":{
        "class":"PhpPlatform\\WebSession\\Session"
    }
}
```
