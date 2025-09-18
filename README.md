![Package cover](./arts/package-cover.png)

# Model Fields

<!-- shields -->
[![Required Laravel Version][ico-laravel]][link-packagist]
[![Required PHP Version][ico-php]][link-packagist]
[![Latest Version on Packagist][ico-version]][link-packagist]
![GitHub Tests For Laravel Versions Action Status][ico-tests-for-laravel-versions]
![GitHub Tests For Databases Action Status][ico-tests-for-databases]
![GitHub Code Style Action Status][ico-code-style]
[![Total Downloads][ico-downloads]][link-downloads]
![GitHub Stars][ico-github-stars]
[![StandWithPalestine][ico-palestine]][link-palestine]

[ico-laravel]: https://img.shields.io/badge/Laravel-%E2%89%A56.0-ff2d20?style=flat-square&logo=laravel

[ico-php]: https://img.shields.io/packagist/php-v/watheqalshowaiter/model-fields?color=%238892BF&style=flat-square&logo=php

[ico-version]: https://img.shields.io/packagist/v/watheqalshowaiter/model-fields.svg?style=flat-square

[ico-downloads]: https://img.shields.io/packagist/dt/watheqalshowaiter/model-fields.svg?style=flat-square&color=%23007ec6

[ico-code-style]: https://img.shields.io/github/actions/workflow/status/watheqalshowaiter/model-fields/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square

[ico-tests-for-laravel-versions]: https://img.shields.io/github/actions/workflow/status/watheqalshowaiter/model-fields/tests-for-laravel-versions.yml?branch=main&label=laravel%20versions%20tests&style=flat-square

[ico-tests-for-databases]: https://img.shields.io/github/actions/workflow/status/watheqalshowaiter/model-fields/tests-for-databases.yml?branch=main&label=databases%20tests&style=flat-square

[ico-github-stars]: https://img.shields.io/github/stars/watheqalshowaiter/model-fields?style=flat-square

[ico-palestine]: https://raw.githubusercontent.com/TheBSD/StandWithPalestine/main/badges/StandWithPalestine.svg

[link-packagist]: https://packagist.org/packages/watheqalshowaiter/model-fields

[link-downloads]: https://packagist.org/packages/watheqalshowaiter/model-fields/stats

[link-palestine]: https://github.com/TheBSD/StandWithPalestine/blob/main/docs/README.md
<!-- ./shields -->

Get the **required** fields fast for any model. You can also get **nullable** and **default** fields just as easily.
Think that's simple? You probably haven’t faced the legacy projects I have. :).

> [!Note]  
> This is the documentation for version 3, if you want the version 1 or version 2 documentations go  
> V1 with [this link](./v1.documentation.md).  
> V2 with [this link](./v2.documentation.md).

## Installation

You can install the package via Composer:

```sh
composer require watheqalshowaiter/model-fields --dev
```

We prefer `--dev` because you usually use it in development, not in production. If you have a use case that requires
using the package in production, then remove the --dev flag.

Optionally, if you want to publish the configuration to disable/enable model macros.

```sh
php artisan vendor:publish --provider="WatheqAlshowaiter\ModelFields\ModelFieldsServiceProvider" --tag="config"
```

## Usage

We Assume that the `User` model has this schema as the default.

```php
Schema::create('users', function (Blueprint $table) {
    $table->id(); // primary key
    $table->string('name'); // required
    $table->string('email')->unique(); // required
    $table->timestamp('email_verified_at')->nullable(); // nullable
    $table->string('password'); // required
    $table->string('random_number'); // default (in model attributes)
    $table->rememberToken(); // nullable
    $table->timestamps(); // nullable
});
```

> [!IMPORTANT]  
> We have two ways:
> - Either use the `ModelFields` facade.
> - Or use the method statically on the model. (using the magic of laravel macros).

We will explain the **macro way** in two examples, and the other will be only using the **facade way** and all the
methods in both ways are the same.

```php
// Facade way
use WatheqAlshowaiter\ModelFields\Fields;
use App\Models\User;

Fields::model(User::class)->allFields(); // returns ['id', 'name', 'email', 'email_verified_at', 'password', 'random_number', 'remember_token', 'created_at', 'updated_at']
Fields::model(User::class)->requiredFields(); // returns ['name', 'email', 'password']
```

```php
// Macro way
User::allFields(); // returns ['id', 'name', 'email', 'email_verified_at', 'password', 'random_number', 'remember_token', 'created_at', 'updated_at']
User::requiredFields(); // returns ['name', 'email', 'password']
```

That's it!

> [!NOTE]  
> To disable the macro approach, set the `enable_macro` value to false in the published `model-fields.php`
> configuration file.

### Another Complex Table

Let's say the `Post` model has these fields

```php
Schema::create('posts', function (Blueprint $table) {
    $table->uuid('id')->primary(); // primary key
    $table->foreignId('user_id')->constrained(); // required
    $table->foreignId('category_id')->nullable(); // nullable
    $table->uuid(); // required (but will be changed later) 👇
    $table->ulid('ulid')->nullable(); // nullable (but will be changed later) 👇
    $table->boolean('active')->default(false); // default
    $table->string('title'); // required
    $table->json('description')->nullable(); // nullable (but will be changed later) 👇
    $table->string('slug')->nullable()->unique(); // nullable
    $table->timestamps(); // nullable
    $table->softDeletes(); // nullable
});

// later migration..
Schema::table('posts', function(Blueprint $table){
    $table->json('description')->nullable(false)->change(); // required
    $table->ulid('ulid')->nullable(false)->change(); // required
    $table->uuid()->nullable()->change(); // nullable
});
```

```php
// Facade way 
Fields::model(Post::class)->requiredFields(); // returns ['user_id', 'ulid', 'title', 'description']
// Macro way
Post::requiredFields();  // returns ['user_id', 'ulid', 'title', 'description']
```

### And more

We have the flexibility to get all fields, required fields, nullable fields, primary key, database default fields,
application default fields, and default fields. You can use these methods with these results:

```php
// All fields
Fields::model(Post::class)->allFields();

// or
Post::allFields();

// returns
// [    'category_id', 'uuid', 'ulid', 'description',
//      'slug', 'created_at', 'updated_at', 'deleted_at'
// ]
```

```php
// Nullable fields 
Fields::model(Post::class)->nullableFields();

//or
Post::nullableFields();

// returns
// [
//     'user_id', 'category_id', 'uuid', 'ulid', 'slug',
//     'created_at', 'updated_at', 'deleted_at'
// ]
```

```php
// Primary field
Fields::model(Post::class)->primaryField();

// or
Post::primaryField();

// returns ['id']
```

```php
// Database default fields
Fields::model(Post::class)->databaseDefaultFields();

//or 
Post::databaseDefaultFields();

// returns ['active']
```

```php
// Application default fields
Fields::model(Post::class)->applicationDefaultFields();

//or 
Post::applicationDefaultFields();

// If there is default attributes in the model
class Post extends Model
{
    protected $attributes = [
        'title' => 'default title', 
        'description' => 'default description',
    ];
}

// returns
// [
//     'title', 'description',
// ]
```

```php
// Default fields
Fields::model(Post::class)->defaultFields();

//or 
Post::defaultFields();

// This will combine application and database defaults 
class Post extends Model
{
    protected $attributes = [
        'title' => 'default title', 
        'description' => 'default description',
    ];
}

// returns
// [
//    'active', 'title', 'description',
// ]
```

## Why?

### The problem

I wanted to add tests to a legacy project that didn't have any. I wanted to add tests but couldn't find a factory, so I
tried building them. However, it was hard to figure out the required fields for testing the basic functionality since
some tables have too many fields.

### The Solution

To solve this, I first created a simple facade class and a trait (which was later removed) to allow direct method usage
on models for retrieving required fields. Later, I added support for older Laravel versions, as most use cases were on
those versions.

So Briefly, This package is useful if:

- you want to build factories or tests for projects you didn't start from scratch.
- you are working with a legacy project and don't want to be faced with SQL errors when creating tables.
- you have so many fields in your table and want to get types of fields fast, like required, nullable, default fields.
- or any use case you find it useful.

## Features

✅ Supports Laravel versions: 12, 11, 10, 9, 8, 7, and 6.

✅ Supports PHP versions: 8.4, 8.3, 8.2, 8.1, 8.0, and 7.4.

✅ Supports SQL databases: SQLite, MySQL/MariaDB, PostgreSQL, and SQL Server.

✅ Fully automated tested with PHPUnit.

✅ Full GitHub Action CI pipeline to format code and test against all Laravel and PHP versions.

✅ Can return fields based on the dynamically added class strings (in the facade method).

## Testing

```sh
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

If you have any ideas or suggestions to improve it or fix bugs, your contribution is welcome.

I encourage you to look at [Issues](https://github.com/WatheqAlshowaiter/model-fields/issues) which are the
most important features that need to be added.

If you have something different, submit an issue first to discuss or report a bug, then do a pull request.

## Security Vulnerabilities

If you find any security vulnerabilities don't hesitate to contact me at `watheqalshowaiter[at]gmail[dot]com` to fix
them.

## Credits

- [Watheq Alshowaiter](https://github.com/WatheqAlshowaiter)

- [All Contributors](https://github.com/WatheqAlshowaiter/model-fields/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
