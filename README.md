# Newoffers Messenger

Newoffers Messenger is a Pub/Sub messaging provider using AWS SNS or Azure ServiceBus

Use an environment variable `MESSENGER` to specify the selected service.

Available options:
- `sns` 
- `serviceBus`

## To use SNS (AWS)
Create a configuration file called `aws` with the following structure:

```php
return [
    'account_id' => string,
    'key' => string,
    'secret' => string,
    'region' => string,
    'version' => string,
];
```

## To use Service Bus (Azure)
Create a configuration file called `azure` with the following structure:

```php
return [
    'service_bus' => [
        'namespace' => string,
        'key_name' => string,
        'key_value' => string
    ]
];
```
