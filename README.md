# php-js

A micro-framework for translating PHP-defined actions into browser actions using jQuery. This library allows you to send commands (we call them "triggers") from your PHP backend to your JavaScript frontend.

## Installation

The recommended way to install this library is through [Composer](https://getcomposer.org/).

```bash
composer require alexbusu/php-js
```

## Usage

### PHP Backend

First, you need to create a `Phpjs` response object in your PHP code. You can then add triggers to this object.

```php
<?php

require 'vendor/autoload.php';

use Alexbusu\Phpjs;

// Get the response object
$response = Phpjs::response();

// Add a message to be displayed to the user
$response->message('Your request was successful!');

// Redirect the user to a new page after 2 seconds
$response->redirect('/new-page', ['timeout' => 2]);

// Log some data to the browser console
$response->consoleLog(['some' => 'data']);

// Trigger a custom event
$response->trigger('custom.event', ['foo' => 'bar']);

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
```

### JavaScript Frontend

Include the provided JavaScript file in your HTML, along with jQuery.

```html
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="vendor/alexbusu/php-js/src/js/jquery.setup.js"></script>
```

The `jquery.setup.js` script will automatically handle JSON responses from AJAX calls and trigger the corresponding events.

For custom triggers, you need to set up your own event handlers.

```javascript
$(document).on('custom.event', function(event, data) {
    console.log('Custom event triggered!');
    console.log(data); // { foo: 'bar' }
});
```

## Built-in Triggers

This library comes with a set of built-in triggers:

- `doc.Status`: Display a message to the user. You need to implement the UI for this yourself.
- `winrd`: Redirect the browser to a new URL.
- `winreload`: Reload the current page.
- `error.console`: Log an error message to the console.
- `warn.console`: Log a warning message to the console.
- `table.console`: Display tabular data in the console.
- `info.console`: Log an informational message to the console.
- `log.console`: Log a general message to the console.
- `set-cookie`: Set a cookie in the browser.

## Custom Triggers

You can define and trigger your own custom events from the PHP backend.

**PHP:**

```php
$response->trigger('my.custom.trigger', '#my-element', ['some' => 'data']);
```

**JavaScript:**

```javascript
$('#my-element').on('my.custom.trigger', function(event, data) {
    // Handle the event
});
```

## Debugging

You can enable debug mode by passing `['debug' => true]` to the `Phpjs` constructor or the `response()` method. When debug mode is enabled, exceptions thrown in PHP will be sent to the browser console with a full stack trace.

```php
// Enable debug mode
$response = Phpjs::response(['debug' => true]);

// ...

// This will now include the stack trace in the response
$response->message(new \Exception('Something went wrong.'));
```
