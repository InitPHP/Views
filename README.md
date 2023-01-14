# InitPHP Views


### Installation

```
composer require initphp/views
```

### Usage

_**Note :** Remember to choose to work with an adapter before using it._

```php
$data = [
    'username'  => 'admin'
];

echo view('dashboard/dashboard', $data);
```

Give a string array to load multiple views.

```php
$data = [
    'username'  => 'admin'
];

echo view(['header', 'content', 'footer'], $data);
```

`$data` can be an associative array or an object.

```php
$data = new stdClass;
$data->username = 'admin';

echo view('dashboard/profile', $data);
```

## Adapters

### PurePHP Adapter

```php
use \InitPHP\Views\Facade\View;
use \InitPHP\Views\Adapters\PurePHPAdapter;

$viewAdapter = new PurePHPAdapter(__DIR__ . '/Views/');

View::via($viewAdapter);
```

__Note :__ This adapter uses `.php` for the extension of the view files. If the view file does not end with `.php` it is added automatically.

__Note :__ This adapter includes the view files as a PHP file at runtime.

### Laravel Blade (Illuminate/View) Adapter

Don't forget to install the relevant packages before you start.

```
composer require illuminate/view
```

To start using the adapter, just generate the Instance of the relevant adapter.

```php
use \InitPHP\Views\Facade\View;
use \InitPHP\Views\Adapters\BladeAdapter;

$viewAdapter = new BladeAdapter(__DIR__ . '/Views/', __DIR__ . '/Cache/');

View::via($viewAdapter);
```

__Note :__ This adapter may have some unique changes. [Docs](https://laravel.com/docs/9.x/blade)

```php
View::directive('now', function ($format = null) {
    return '<?php echo '
            . ($format === null ? 'date("Y-m-d H:i:s")' : 'date(' . $format . ')')
            . ' ?>';
});

// @now
// @now("Y-m-d")
```

### Symfony Twig (Twig/Twig) Adapter

Don't forget to install the relevant packages before you start.

```
composer require twig/twig
```

To start using the adapter, just generate the Instance of the relevant adapter.

```php
use \InitPHP\Views\Facade\View;
use \InitPHP\Views\Adapters\TwigAdapter;

$viewAdapter = new TwigAdapter(__DIR__ . '/Views/', __DIR__ . '/Cache/');

View::via($viewAdapter);
```

__Note :__ Note that the Twig engine accepts any file extension and you have to specify it manually. [Docs](https://twig.symfony.com/doc/3.x/templates.html)

```php
$data = [
    'username'  => 'admin'
];

echo view('dashboard/dashboard.html', $data);
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Copyright &copy; 2022 [MIT License](./LICENSE)
