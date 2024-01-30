# Statamic Events

> Statamic Events is a Statamic addon that lets you run actions when events are triggered.

## How to Install

Run the following command from your project root:

``` bash
composer require tv2regionerne/statamic-events
```

## Configuration

A configuration file can be published by running the following command:

`php artisan vendor:publish --tag=statamic-events`

This will create `statamic-events ` in your config folder.


### Extra events

By default this add-on will list events in the Statamic\Events namespace, but you can change this or configure other events to be listed by modifying the `statamic-events.events` config. This should be a list of folders relative to the base folder, keyed by their namespace. 

After changing this config, you should run `php artisan cache:clear`.


### Response handlers

Each driver allows you to specify `response_handlers` which can be used to run any additional processing. This should be an array of unique keys to fully qualified class names, eg `['my_key' => '\App\Handlers\MyHandler::class']`

A handler is a class containing a handle method:

```php
class MyHandler 
{
   public handle(array $config, string $eventName, mixed $event, mixed $response = null) 
   {
      // run some logic
      // you may want to $execution->log(string $message, array $data) something 
   }
}
```

