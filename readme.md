# PHP Laravel Support
![](https://img.shields.io/badge/php->=7.2-blue.svg)
[![Build Status](https://travis-ci.org/efureev/laravel-support.svg?branch=master)](https://travis-ci.org/efureev/laravel-support)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a53fb85fd1ab46169758e10dd2d818cb)](https://app.codacy.com/app/efureev/laravel-support?utm_source=github.com&utm_medium=referral&utm_content=efureev/laravel-support&utm_campaign=Badge_Grade_Settings)
[![Latest Stable Version](https://poser.pugx.org/efureev/support/v/stable?format=flat)](https://packagist.org/packages/efureev/support)
[![Total Downloads](https://poser.pugx.org/efureev/support/downloads)](https://packagist.org/packages/efureev/support)
[![Maintainability](https://api.codeclimate.com/v1/badges/a7cf8708bf58fa7e5096/maintainability)](https://codeclimate.com/github/efureev/laravel-support/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/a7cf8708bf58fa7e5096/test_coverage)](https://codeclimate.com/github/efureev/laravel-support/test_coverage)
[![codecov](https://codecov.io/gh/efureev/laravel-support/branch/v2/graph/badge.svg)](https://codecov.io/gh/efureev/laravel-support/tree/v2)

## Install
```bash
composer require efureev/laravel-support "^0.0.2"
```


## Usage

### Traits\Database\UUID

Use UUID type for primary key
```php
Schema::create(
    'table_name',
    static function (Blueprint $table) {
        static::columnUUID($table)->primary();
        $table->string('title');
        $table->timestamps();
    }
);
```
Use UUID type for foreign keys
```php
Schema::create(
    'table_name',
    static function (Blueprint $table) {
        //...
        static::columnUUID($table, 'source_id', false)->nullable()->index();
        //...
    }
);
```
Types of `$default`:
- string: `NOW()`
- callable: needs return param of `Illuminate\Database\Query\Expression` class 
- class `Illuminate\Database\Query\Expression`


### Traits\Models\PostgresArray
Scope for searching into PG arrays.
@see: `\Php\Support\Laravel\Tests\Models\PgArrayModel::scopeByTag`

### Custom Casting
Use it for custom casting model's attributes. Even based on classes.
@see: `\Php\Support\Laravel\Tests\Models\PgArrayModel::36`
@see: `\Php\Support\Laravel\Tests\Models\TestModel::33`
@example:
Cast class:
```php
use Php\Support\Laravel\Caster\AbstractCasting;

class Params extends AbstractCasting
{
    protected $key;
    
    public function toArray(): array
    {
        return [
            'key'       => $this->key,
        ];
    }
}
```

Model:
```php
use Php\Support\Laravel\Caster\HasCasts;
class TestModel extends Model
{
    use HasCasts;

    protected $table = 'test_table';

    protected $fillable = ['params'];

    protected $casts = [
        'params'    => Params::class,
    ];
}
```
It's enough for use attribute `params` as class `Params`: `$model->params->key`!

Now, mutators are not needed. But they will work. 


## Test
```bash
composer test
composer test-cover # with coverage
```
