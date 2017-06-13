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

## Configuration
#### salt
salt is used to encrypt session file name from actual session id
```php
$sessionFileName = md5($salt.$sessionId);
```

#### path
path is the uri path on which this session cookie must be set, this value is sent as Set-Cookie's path parameter

#### timeout
Session timeout in seconds , this value is used to calculate Set-Cookie's expires and Max-Age parameters 

#### name
name of the session , this is the cookie name sent to the client

#### sessionFilePrefix
this is the prefix for session file names for this application , since all the session files are stored in same directory , this helps to categories the session files for each application

## Scripts
#### delete-expired-sessions

to delete the expired session files , run this script (manually or in a cron job) in regular intervals
```
$ ./vendor/bin/delete-expired-sessions
```
