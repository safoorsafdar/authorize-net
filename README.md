# Filterable

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Filterable is an package helps to filter the eloquent model based on various condition which already ship with the package.   

## Install

Via Composer

``` bash
$ composer require safoorsafdar/filterable
```

## Usage

### Model
``` php
<?php
namespace App\Models\Account;

use SafoorSafdar\Filterable\Traits\FilterableTrait;
use Illuminate\Database\Eloquent\Model;
/**
 * Class Account
 *
 * @package App\Models\Account
 */
class Account extends Model
{

    use FilterableTrait
    
    protected static $filters
        = [
            "name"         => \App\Models\Account\Filters\AccountNameFilter::class,
        ];
}
```
*Note* `$filterable` array contain the current table attribute name in the database and reference to filter class which will performed the query.
### Filter Class
```php
<?php
namespace App\Models\Account\Filters;

use Illuminate\Database\Eloquent\Builder;
use SafoorSafdar\Filterable\Filter\Filter;

class AccountNameFilter extends Filter
{
    /**
     * Apply a given search value to the builder instance.
     *
     * @param Builder $builder
     * @param mixed   $value
     *
     * @return Builder $builder
     */
    public static function apply(
        Builder $builder,
        $value,
        $condition,
        $operator
    ) {
        $operatorDecorator = self::createOperatorDecorator($condition);
        if (self::isValidDecorator($operatorDecorator)) {
            return app($operatorDecorator)->resolve($builder, 'name', $value,
                $operator);
        }
    }
}
```
### Controller
```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function index(){
        $filters = $request->get('filter', []);
        return view('account.index')
            ->withFilterFields(\App\Models\Account\Account::filterableAttributes())
            ->withFilterCondition(\Filterable::operators())
            ->with("filtered", $filters); 
    }
    
}
```
### View
```
@include('Filterable::partial.filterable',['filtered'=>$filtered,'filter_fields'=>$filter_fields,'filter_condition'=>$filter_condition])
<script type="text/javascript" src="/js/filterable.js"></script>
```
### Apply submitted Filter to model
```php
$query     = Account::with(['user']);
$filters   = array_filter(array_get($request->all(), 'filter', []));
if ( ! empty($filters)) {
   $query->applyFilter($filters);   
}
$result = $query->get();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email safoor.safdar@gmail.com instead of using the issue tracker.

## Credits

- [SafoorSafdar][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/SafoorSafdar/Filterable.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/SafoorSafdar/Filterable/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/SafoorSafdar/Filterable.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/SafoorSafdar/Filterable.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/SafoorSafdar/Filterable.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/SafoorSafdar/Filterable
[link-travis]: https://travis-ci.org/SafoorSafdar/Filterable
[link-scrutinizer]: https://scrutinizer-ci.com/g/SafoorSafdar/Filterable/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/SafoorSafdar/Filterable
[link-downloads]: https://packagist.org/packages/SafoorSafdar/Filterable
[link-author]: https://github.com/safoorsafdar
[link-contributors]: ../../contributors
