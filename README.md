# MockServer Behat context

Php client and behat context for [MockServer](https://www.mock-server.com/).

## Install

From [Packagist](https://packagist.org/packages/lequipefr/mockserver-behat-context):

``` bash
composer require --dev lequipefr/mockserver-behat-context
```

Then add a context in your `behat.yml`, with the url to your local MockServer instance:

``` yml
default:
    suites:
        default:
            contexts:

                # Add this:
                - Lequipe\MockServer\Behat\MockServerContext:
                    mockServer: 'http://127.0.0.1:1080'
```

Now, in your behat tests, you should be able to mock the webservices your project depends to, with:

```
Given the request "GET" "/users/1" will return the json:
    """
    {
        "id": 1,
        "name": "Zidane"
    }
    """
```

## Usage

### Behat context

See [all available behat phrases you can use](docs/behat-phrases.md).

### PHP client

You can also use this library as a simple PHP client,
and send your expectations as raw arrays, as defined in
[mockserver swagger api](https://app.swaggerhub.com/apis/jamesdbloom/mock-server-openapi/5.12.x#/expectation/put_expectation):

``` php
<?php

use Lequipe\MockServer\Client\MockServerClient;

$client = new MockServerClient('http://127.0.0.1:1080');

$client->expectation([
    'httpRequest' => [
        'method' => 'GET',
        'path' => '/users/1',
    ],
    'httpResponse' => [
        'body' => [
            [
                'id' => 1,
                'name' => 'Zidane',
            ],
        ],
    ],
]);
```

### Builder

Instead of raw arrays, you can use the expectation builder and let your IDE autocomplete:

``` php
<?php

use Lequipe\MockServer\MockServerClient;
use Lequipe\MockServer\Builder\Expectation;

$client = new MockServerClient('http://127.0.0.1:1080');

$expectation = new Expectation();

$expectation->httpRequest()
    ->method('GET')
    ->path('/users/1')
;

$expectation->httpResponse()
    ->bodyJson([
        [
            'id' => 1,
            'name' => 'Zidane',
        ],
    ])
;

$client->expectation($expectation);
```


## Configuration example

You may want to configure your project so that in test environment, external API are replaced by MockServer.

For example, you may have this kind of configuration:

`.env`: points to real api

```
USERS_API=https://users-api.local
```

`.env.test`: points to MockServer instance

```
USERS_API=http://127.0.0.1:1080
```

### Multiple services

If you have multiple apis or microservices
and want to make sure a request is sent to the expected service,
you can use one of these following trick:

#### Custom path prefix

`.env`:
```
USERS_API=https://users-api.local
OTHER_API=https://other-api.com
```

`.env.test`:
```
USERS_API=http://127.0.0.1:1080/users-api/
OTHER_API=http://127.0.0.1:1080/other-api/
```

This way, when running behat tests,
your application will hit mockserver in any case,
but with a different path prefix.

Then, your tests will looks like:

``` cucumber
Given the request "GET" "/users-api/users/1" will return the json: ...
Given the request "GET" "/other-api/v1/something" will return the json: ...
```

#### Custom domain name

One drawback of the solution above is that
it can be difficult to concatenate a base host with a relative path
if you didn't handled this case since the beginning,
and always assumed that you hit your api at the root.

So in this case you can use different hosts name:

`.env`:
```
USERS_API=https://users-api.local
OTHER_API=https://other-api.com
```

`.env.test`:
```
USERS_API=https://users-api.mockserver:1080
OTHER_API=https://other-api.mockserver:1080
```

If you do the necessary in your hosts name resolution to make
any `*.mockserver` requests will hit your localhost,
your mockserver instance will receive all requests with a different hostname
that you can match with the `httpRequest.headers[name = 'Host']` parameter in expectation.

## FAQ

### I am still getting 404's when my application under test is trying to query mocked external service

For all trouble you get between your application and mockserver, you should use MockServer UI.

It is already installed and accessible here: <http://127.0.0.1:1080/mockserver/dashboard>.

If not, check [MockServer UI documentation](https://www.mock-server.com/mock_server/mockserver_ui.html).

The UI displays useful information:

- Active expectations, the mocks received,
- Received Requests, the requests your tested application have sent to mockserver instead of the real external service
- Log messages, all mocks, requests received, did the mock have been sent, if not, why...

If you get 404's, you may check all this points:

- Did mockserver have received the mock ?

In "Active Expectations" you must see the mock you defined in your behat test.
If not, check if the mockserver url in `behat.yml` valid and used:

``` yml
                - Lequipe\MockServer\Behat\MockServerContext:
                    mockServer: 'http://127.0.0.1:1080'
```

- Did the appplication called mockserver

In "Received Requests", you must see the request sent by your tested application.
If not, check where is sent the request then. You should configure your application
to use mockserver as base host instead the external service, something like:

```
# in .env file, real service:
USERS_API=https://users-api.local

# .env.test file, mockserver:
USERS_API=https://127.0.0.1:1080
```

- Did the request matched the mock

If "Active Expectations" and "Received Requests" contains your mock and a received request,
did the request matched the mock ?

Mockserver can contains multiple mocks and will response with the mock
only if path, headers... matches.

To check that, in "Log Messages" section, you should see `MATCHED_EXPECTATION`.
If not, check the log line about the received request, it will tell you with the request didn't matched the mock.

Sometimes it didn't match just because slash in path is like `api/...` instead of `/api/...`.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

This library is under [MIT license](LICENSE).
