##[JSON-RPC 2.0](http://www.jsonrpc.org/specification) for Yii2 API with CRUD+ actions

## Table of Contents
 - [Features](#features)
 - [Using](#using)
 - [CRUD+ API actions](#crud-api-actions)
  - [Example Create](#example-create)
  - [Example Update](#example-update)
  - [Example Delete](#example-delete)
  - [Example DeleteAll](#example-delete-all)
  - [Example View](#example-view)
 - [List API action](#list-api-action)
  - [Example List](#example-list)

## Features:

1. CRUD API actions
2. List API action
3. DeleteAll API action
4. CORS Support


## Using
Easiest way to use in 4 steps:<br/>

1. Install via composer

    in ./composer.json add into 'require' section
    ~~~javascript
        "studio451/yii2-json-rpc-2.0-API": "1.*"
    ~~~
    and in console/terminal run
    ~~~php
    composer update
    ~~~
2. Use namespace in your controller for CRUD+ actions

    ~~~php
    use \jsonrpc2API\ActiveController;
    ~~~
    change extends class to
    ~~~php
    class ModelController extends \jsonrpc2API\ActiveController {
    
        public $modelClass = 'app\models\Model';    
        public $dataFilter = 'app\models\ModelFilter';

        //BODY or empty
        ~~~
    }
    ~~~

3. Make json request to controller (used pretty urls without index.php)
    >Request method MUST be POST and Content-type MUST be application/json

## CRUD+ API actions

For call JsonRPC API you may use this function:

~~~javascript
function sendToJsonrpc2(url, method, params, callback){
    
    let command = {"jsonrpc": "2.0", "id": createUUID(), "method": method, "params": params};

    command = JSON.stringify(command);

    let request = {
        type: "POST",
        dataType: 'JSON',
        url: '/jsonrpc2/v1/' + url + '?access-token=wKiPsMKbAm1a7Gg09rZ2qg28UWyWMiXN',
        contentType: "application/json",
        data: command
    };

    $.ajax(request).done(function (data) {
        if (callback)
        {
            callback(data);
        }
    });
}
function createUUID() {
    const fmt = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx';
    const rnd = Array.from(crypto.getRandomValues(new Uint8Array(32))).map(n => n & 0xf);
    const rk = (c, r) => ((c == 'x' ? r : (r & 0x3 | 0x8)).toString(16));
    return fmt.replace(/[xy]/g, c => rk(c, rnd.pop()));
}
~~~



CreateAction

### Example
    request
    ~~~javascript
    {
  "type": "POST",
  "dataType": "JSON",
  "url": "/jsonrpc2/v1/author",
  "contentType": "application/json",
  "data": {
    "jsonrpc": "2.0",
    "id": "6187d757-9a9b-469b-87d7-54b080304099",
    "method": "create",
    "params": {
      "firstName": "Ray",
      "secondName": "Bradbury"
    }
  }
}
    ~~~
    and response will be
    ~~~javascript
    {
  "jsonrpc": "2.0",
  "id": "6187d757-9a9b-469b-87d7-54b080304099",
  "method": "create",
  "timestamp": 1559548947,
  "result": {
    "firstName": "Ray",
    "secondName": "Bradbury",
    "id": 1,
    "books": []
  }
}
    ~~~

UpdateAction
### Example
    request
    ~~~javascript
{
  "type": "POST",
  "dataType": "JSON",
  "url": "/jsonrpc2/v1/author",
  "contentType": "application/json",
  "data": {
    "jsonrpc": "2.0",
    "id": "982f4f47-e72e-4b3c-90f5-f8422a5f90b1",
    "method": "update",
    "params": {
      "id": 1,
      "description": "Ray Douglas Bradbury"
    }
  }
}
 ~~~
    and response will be
    ~~~javascript
{
  "jsonrpc": "2.0",
  "id": "982f4f47-e72e-4b3c-90f5-f8422a5f90b1",
  "method": "update",
  "timestamp": 1559548983,
  "result": {
    "id": 1,
    "firstName": "Ray",
    "secondName": "Bradbury",
    "description": "Ray Douglas Bradbury",
    "time": 0,
    "status": 1,
    "books": []
  }
}
~~~
ViewAction
### Example
    request
    ~~~javascript

{
  "type": "POST",
  "dataType": "JSON",
  "url": "/jsonrpc2/v1/author",
  "contentType": "application/json",
  "data": {
    "jsonrpc": "2.0",
    "id": "3670b63a-55d3-40a9-965e-7f61a72858b0",
    "method": "view",
    "params": {
      "id": 1
    }
  }
}
~~~
    and response will be
    ~~~javascript
{
  "jsonrpc": "2.0",
  "id": "3670b63a-55d3-40a9-965e-7f61a72858b0",
  "method": "view",
  "timestamp": 1559549627,
  "result": {
    "id": 1,
    "firstName": "Ray",
    "secondName": "Bradbury",
    "description": null,
    "time": 0,
    "status": 1,
    "books": [
      {
        "id": 1,
        "id_author": 1,
        "title": "Fahrenheit 451",
        "description": null,
        "time": 0,
        "status": 1
      }
    ]
  }
}
~~~
DeleteAction
### Example
    request
    ~~~javascript

{
  "type": "POST",
  "dataType": "JSON",
  "url": "/jsonrpc2/v1/author",
  "contentType": "application/json",
  "data": {
    "jsonrpc": "2.0",
    "id": "5e53b745-25f4-4039-87ab-e72acc5fd827",
    "method": "delete",
    "params": {
      "id": 1
    }
  }
}
~~~
    and response will be
    ~~~javascript
{
  "jsonrpc": "2.0",
  "id": "5e53b745-25f4-4039-87ab-e72acc5fd827",
  "method": "delete",
  "timestamp": 1559549032,
  "result": true
}
~~~
and DeleteAllAction
### Example
    request
    ~~~javascript

{
  "type": "POST",
  "dataType": "JSON",
  "url": "/jsonrpc2/v1/book",
  "contentType": "application/json",
  "data": {
    "jsonrpc": "2.0",
    "id": "cd340790-fce7-468b-a5dd-47f1e0a227c0",
    "method": "delete-all",
    "params": {}
  }
}
~~~
    and response will be
    ~~~javascript
{
  "jsonrpc": "2.0",
  "id": "cd340790-fce7-468b-a5dd-47f1e0a227c0",
  "method": "delete-all",
  "timestamp": 1559550202,
  "result": true
}
~~~

## List API action

Based on Yii2 ActiveDataProvider and used jsonrpc2API\ActiveController::dataFilter

### Example
    request
    ~~~javascript

{
  "type": "POST",
  "dataType": "JSON",
  "url": "/jsonrpc2/v1/book",
  "contentType": "application/json",
  "data": {
    "jsonrpc": "2.0",
    "id": "d159328b-7b55-4b3b-88d5-f97eafce13f9",
    "method": "list",
    "params": {
      "filter": {
        "and": [
          {
            "title": {
              "like": "Fah"
            }
          },
          {
            "id_author": 1
          }
        ]
      },
      "pagination": {
        "pageSize": 25
      },
      "sort": {
        "defaultOrder": {
          "id": 4
        }
      }
    }
  }
}
~~~
    and response will be
    ~~~javascript
{
  "jsonrpc": "2.0",
  "id": "d159328b-7b55-4b3b-88d5-f97eafce13f9",
  "method": "list",
  "timestamp": 1559549743,
  "result": {
    "models": [
      {
        "id": 1,
        "id_author": 1,
        "title": "Fahrenheit 451",
        "description": null,
        "time": 0,
        "status": 1
      }
    ],
    "count": 1,
    "totalCount": 1
  }
}
~~~

<br/>
<br/>
#####Live demo https://yii2jsonrpc2api.studio451.ru
#####Source of live demo https://github.com/studio451/yii2-json-rpc-2.0-API-live-demo