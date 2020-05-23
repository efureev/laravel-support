# PHP Laravel Support
![](https://img.shields.io/badge/php->=7.3-blue.svg)
![](https://img.shields.io/badge/Laravel->=7.0-red.svg)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/5c8b9e85897f4c65b5a017d16f6af6cb)](https://app.codacy.com/manual/efureev/laravel-support?utm_source=github.com&utm_medium=referral&utm_content=efureev/laravel-support&utm_campaign=Badge_Grade_Dashboard)
[![Build Status](https://travis-ci.com/efureev/laravel-support.svg?branch=master)](https://travis-ci.com/efureev/laravel-support)
![PHP Laravel Package](https://github.com/efureev/laravel-support/workflows/PHP%20Laravel%20Package/badge.svg?branch=master)
[![Latest Stable Version](https://poser.pugx.org/efureev/laravel-support/v/stable?format=flat)](https://packagist.org/packages/efureev/laravel-support)
[![Total Downloads](https://poser.pugx.org/efureev/laravel-support/downloads)](https://packagist.org/packages/efureev/laravel-support)
[![Maintainability](https://api.codeclimate.com/v1/badges/5c2f433a24871b1f12e3/maintainability)](https://codeclimate.com/github/efureev/laravel-support/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/5c2f433a24871b1f12e3/test_coverage)](https://codeclimate.com/github/efureev/laravel-support/test_coverage)

## Install
```bash
composer require efureev/laravel-support "^0.5"
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
