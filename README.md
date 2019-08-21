# Symfony testing bundle for Doctrine repositories

This Symfony Bundle provides easy way to quickly test all your Doctrine repositories.

Each repository method will be tested against query misconception and usage of values unexisting in your entities schema.

You can use either or both of a Symfony command to manually get a reporting and a simple integration to your PHPUnit test suite.

## Requirements

* `PHP >= 7.1`
* `symfony >= 3.4`

## Installation 

```
composer require --dev beapp/repository-tester-bundle
```

That's all, you're ready to use it now !

## Pageable integration

In case you already use our doctrine pagination library, we also provide an integration of Pageable classes to these tests.
You must so require this package :

```
composer require --dev beapp/repository-tester-pageable
```

### Configuration (with Pageable integration)

You will need to override some service configuration in order to use the `PageableParamBuilder` :

```
    TODO example
```

Now you're ready to use it !

## Getting started 

To start testing your repositories manually, use this command :

```
php bin/console beapp:repositories:test
```

Or to include it to your units tests, put it directly into your phpunit.xml file :

```
<phpunit>
    <testsuites>
        <testsuite name="Your awesome suite">
            <file>vendor/beapp/repository-tester-bundle/src/Test/RepositoryTest.php</file>
        </testsuite>
    </testsuites>
</phpunit>
```

Now every time you will run your tests, your repositories will also be tested (each repository method generate one phpunit test).
