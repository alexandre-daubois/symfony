Query Component
===============

The Query component provides an object-oriented API to query in-memory collections in a SQL-style.

Usage
-----

Here is the set of data we're going to use in the following examples:

```php
$cities = [
    new City('Lyon', [
        new Person([
            new Child('Hubert', age: 30),
            new Child('Aleksandr', age: 18),
            new Child('Alexandre', age: 26),
            new Child('Alex', age: 25),
        ], height: 181),
        new Person([
            new Child('Fabien', age: 23),
            new Child('Nicolas', age: 8),
        ], height: 176),
        new Person([
            new Child('Alexis', age: 33),
            new Child('Pierre', age: 5),
        ], height: 185)
    ], minimalAge: 21),
    new City('Paris', [
        new Person([
            new Child('Will', age: 33),
            new Child('Alix', age: 32),
            new Child('Alan', age: 45),
        ], height: 185)
    ], minimalAge: 45)
];
```

## The Query object

The `Query` object allows you to easily fetch deep information in your collections with ease. Just like Doctrine's `QueryBuilder`, plenty of utils methods are present for easy manipulation.

Moreover, you're able to create your query step-by-step and conditionally if needed. To create a simple query, all you need is create a new instance of the `Query` object and pass your collection as its source, thanks to the `from` method:

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city');
```

From here, you're able to manipulate your collections and fetch data. First, let's see how to filter your collections. Note that the `city` argument is optional and defines an alias for the current collection. By default, the alias is `_`. Each alias must be unique in the query, meaning it is mandatory you pass an alias if you are dealing with deep collections.

## Modifiers (filtering, ordering, limiting, etc.)

Modifiers allow to filter results, order them, limit them and so on.

> 🔀 Modifiers can be given to the query in any order, as they are only applied when an operation is called.

### Where

#### Usage

`where`, which takes an expression as an argument. Internally, `where` uses Symfony's ExpressionLanguage component. You are able to do anything the component is able to do. For more information, please refer to the [ExpressionLanguage component documentation](https://symfony.com/doc/current/components/expression_language/syntax.html).

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city')
    ->where('city.name contains "Lyon" or city.name in ["Paris", "Rouen"]');
```

#### Passing additional environment

Sometimes you may want to pass additional environment to your `where` call. This could be variables or even the result of another `Query`. This can be done thanks to the second argument of the `where` modifier.

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city')
    ->where('_.name in listOfCities', [
        'listOfCities' => [
            'Lyon',
            'Grenoble',
            'Saint-Tropez',
        ],
    ]);
```

#### Registering custom functions

As the `where` modifier takes fully advantage of the ExpressionLanguage component, you are also able to globally register custom functions to be available for all your queries.

```php
use App\Query\Query;

Query::registerWhereFunction(ExpressionFunction::fromPhp('strtoupper'));

$query = (new Query())
    ->from($cities, 'city')
    ->where('strtoupper(city.name) contains "LYON"');
```

You can learn more about this process by taking a look at [Extending the ExpressionLanguage documentation](https://symfony.com/doc/current/components/expression_language/extending.html).

### Order by

`orderBy`, which will order the collection. If the collection only contains scalar values, then you only have to pass an order. If your collection contains objects, you have to pass the order as well as the field to order on. Available orders are: `QueryOrder::Ascending`, `QueryOrder::Descending`, `QueryOrder::None` and `QueryOrder::Shuffle`.

```php
use App\Query\Query;
use App\Query\QueryOrder;

$query = (new Query())
    ->from($cities, 'city')
    ->orderBy(QueryOrder::Ascending, 'name');
```

### Offset

`offset` modifier changes the position of the first element that will be retrieved from the collection. This is particularly useful when doing pagination, in conjunction with the `limit` modifier. The offset must be a positive integer, or `null` to remove any offset.

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city');

// Skip the 2 first cities of the collection and fetch the rest
$query->offset(2)
    ->select();

// Unset any offset, no data will be skipped
$query->offset(null)
    ->select();
```

### Limit

The `limit` modifier limit the number of results that will be used by different operations, such as `select`. The limit must be a positive integer, or `null` to remove any limit.

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city');

// Only the first 2 results will be fetched by the `select` operation
$query->limit(2)
    ->select();

// Unset any limitation, all matching results will be used in the `select` operations
$query->limit(null)
    ->select();
```

## Operations

Operations allow you to fetch filtered data in a certain format. Here is a list of the available operations and how to use them.

### Select

This is the most basic operation. It returns filtered data of the query. It is possible to pass the exact field we want to retrieve, as well as multiple fields. If no argument is passed to `select`, it will retrieve the whole object. You must not pass any argument when dealing with scalar collections.

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city');

// Retrieve the whole object
$query->select();

// Retrieve one field
$query->select('name');

// Retrieve multiple fields
$query->select(['name', 'minimalAge']);
```

### Select One

When querying a collection, and we know in advance that only one result is going to match, this could be redundant to use `select` and retrieve result array's first element everytime. `selectOne` is designed exactly for this case. The behavior of this operation is the following:

* If a single result is found, it will be returned directly without enclosing it in an array of 1 element.
* If no result is found, the `selectOne` operation returns `null`.
* If more than on result is found, then a `NonUniqueResultException` is thrown.

```php
use App\Exception\NonUniqueResultException;

$query = (new Query())
    ->from($cities, 'city')
    ->where('city.name == "Lyon"');

try {
    $city = $query->selectOne(); // $city is an instance of City

    // You can also query a precise field
    $cityName = $query->selectOne('name'); // $cityName is a string
} catch (NonUniqueResultException) {
    // ...
}
```

### Select Many

This operation allows you to go deeper in a collection. Let's say your collection contains many objects with collections inside them, this is what you're going to use to fetch and filter collections.

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city')
    ->where('city.name in ["Paris", "Rouen"]')
    ->selectMany('persons', 'person')
        ->where('person.height >= 180')
        ->selectMany('children', 'child')
            ->where('child.name starts with "Al" and child.age >= city.minimalAge');
```

Like `from`, `selectMany` also takes an alias as an argument. This way, you will be able to reference ancestors in your `where` calls, as shown in the above example.

### Count

This operation returns the size of the current filtered collection:

```php
$query = (new Query())
    ->from($cities, 'city');

$query->count();
```

### Concat

This operation will concatenate the collection with a given separator. If you're dealing with a scalar collection, there is no mandatory argument. If dealing with collections of objects, the `field` argument must be passed.

```php
$query = (new Query())
    ->from($cities, 'city');

$query->concat(', ', 'name');
```

### Each

This operation allows you to pass a callback, which will be applied to each element of the filtered collection. You can see this as a `foreach`.

```php
$query = (new Query())
    ->from($cities, 'city');

// Append an exclamation point to every city name
$query->each(fn($element) => $element->name.' !');
```

### Min and Max

These operations will return the maximum and the minimum of the collection. You can use this on scalar collections. Internally, these operations use `min()` and `max()` functions of the Standard PHP Library, so the same rules apply.

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city')
        ->selectMany('persons', 'person')
            ->selectMany('children', 'child');

$query->min('age'); // 5
$query->max('age'); // 45
$query->min('name'); // "Alan"
$query->max('name'); // "Will"
```

### Sum

`sum` returns the sum of a collection. If the collection contains objects, a field must be provided in order to calculate the sum of it. This only works with collections of numerics, and an exception is thrown if any item of the collection returns `false` to the `\is_numeric()` function.

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city')
        ->selectMany('persons', 'person')
            ->selectMany('children', 'child');

$query->sum('age');
```

### Average

`average` returns the average of a collection. If the collection contains objects, a field must be provided in order to calculate the average of it. This only works with collections of numerics, and an exception is thrown if any item of the collection returns `false` to the `\is_numeric()` function.

```php
use App\Query\Query;

$query = (new Query())
    ->from($cities, 'city')
        ->selectMany('persons', 'person')
            ->selectMany('children', 'child');

$query->average('age');
```

Resources
---------

* [Contributing](https://symfony.com/doc/current/contributing/index.html)
* [Report issues](https://github.com/symfony/symfony/issues) and
  [send Pull Requests](https://github.com/symfony/symfony/pulls)
  in the [main Symfony repository](https://github.com/symfony/symfony)
