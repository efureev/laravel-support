# Caster

- for one entity
- for collection

## For Entity

Cast property into Class for one Entity.

## For Collection

Cast property into Class for collection of items (class / simple).

### Examples #1

Collection for no-wrap elements (example: array):
```php
class ArrayCollection extends AbstractCastingCollection
{
}
```
```json
[{"id": "key","title": "value"}]
```

Collection for wrap elements (example: other class):
```php
class ComponentCollection extends AbstractCastingCollection
{
    protected function wrapEntity(): ?callable
    {
        return static function ($item) {
            return new Component($item);
        };
    }
}
```

```json
[{"id": "key","title": "value"}]
```
```json
{"key1":{"id": "key2","title": "value"},"key2":{"id": "key2","title": "value2"}}
```

Model:
```php
/**
 * @property ComponentCollection $components
 * @property ArrayCollection $arrays
 */
class UserModel extends Model
{
    use HasCasts;

    protected $table = 'test_table_caster';

    protected $casts = [
        'components' => ComponentCollection::class,
        'arrays'     => ArrayCollection::class,
    ];

    protected $fillable = [
        'components',
        'arrays',
    ];
}
```

### Examples #2: Postgres array casting

Model:
```php
class PgArrayModel extends Model
{
    use HasCasts; // <- for casting
    use PostgresArray; // <- for scope byTag($tag)

    protected $table   = 'pg_table';

    
    protected $casts = [
        'tags' => PgArray::class,
    ];

    public function scopeByTag(Builder $query, string $value)
    {
        $this->scopeWherePgArrayContains($query, 'tags', $value);
    }
}
```
```json
['value', 'val2', 'value3']
```
