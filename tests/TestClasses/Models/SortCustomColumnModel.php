<?php

namespace Php\Support\Laravel\Tests\TestClasses\Models;

/**
 * Class SortCustomColumnModel
 * @package Php\Support\Laravel\Tests\TestClasses\Models
 *
 * @property int $sp
 */
class SortCustomColumnModel extends SortEntity
{
    protected $keyType = 'uuid';

    protected $table = 'sort_entities_custom_col';

    protected static $sortingColumnName = 'sp';
}
