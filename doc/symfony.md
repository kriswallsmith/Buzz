[<-- Index](/doc/index.md)

# Symfony integration

Symfony is a great PHP framework and of course should we have some nice integration 
with it. We have provided a flex recipe in the [contrib repository](https://github.com/symfony/recipes-contrib) 
which will register the `Browser` and clients as services. But of course, you want
more! You want a proper bundle. 

Buzz is compatible with HTTPlug which means that we can use all the greatness from 
the [HTTPlugBundle](https://github.com/php-http/httplugbundle). 

## Install 

```
composer require php-http/httplug-bundle
```

## Configure

```
# config/services.yaml
# This is done by the flex recipe
services: 
    Buzz\Browser: 
        calls:
            - ['addMiddleware', ['@buzz.middleware.content_length']]
    
    buzz.middleware.content_length:
        class: Buzz\Middleware\ContentLengthMiddleware
```

```
# config/httplug.yaml
httplug:
    clients:
        my_buzz:
            service: 'Buzz\Browser' 
```

You will now have a service named `httplug.client.my_buzz`. You can of course add 
plugins method clients and whatever you want according to the 
[HTTPlug documentation](http://docs.php-http.org/en/latest/integrations/symfony-bundle.html).


---

Go back to [index](/doc/index.md).