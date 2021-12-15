# Http

## LaraRequest

Helps you build your own Requests.

### Example

You need to boot `LaraRequestServiceProvider`.

Custom Request

```php
<?php

class CustomRequest extends \Php\Support\Laravel\Http\LaraRequest
{
    public function id()
    {
        return $this->query->get('id');
    }
}
```

Controller

```php
<?php

class CustomController extends Controller
{
    public function getPath(CustomRequest $request)
    {
        return $request->id();
    }
}
```

Other services

```php
$app['request'] instanceOf CustomRequest === true
request() instanceOf CustomRequest === true
app('request') instanceOf CustomRequest === true
```
