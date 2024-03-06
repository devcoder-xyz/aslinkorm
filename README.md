# ASLinkORM
ASLinkORM  is a lightweight Object-Relational Mapping (ORM) library that allows you to interact with your database using object-oriented models. This library is designed to simplify the process of managing database records and relationships in your PHP application.

[![Latest Stable Version](http://poser.pugx.org/alphasoft-fr/aslinkorm/v)](https://packagist.org/packages/alphasoft-fr/aslinkorm) [![Total Downloads](http://poser.pugx.org/alphasoft-fr/aslinkorm/downloads)](https://packagist.org/packages/alphasoft-fr/aslinkorm) [![Latest Unstable Version](http://poser.pugx.org/alphasoft-fr/aslinkorm/v/unstable)](https://packagist.org/packages/alphasoft-fr/aslinkorm) [![License](http://poser.pugx.org/alphasoft-fr/aslinkorm/license)](https://packagist.org/packages/alphasoft-fr/aslinkorm) [![PHP Version Require](http://poser.pugx.org/alphasoft-fr/aslinkorm/require/php)](https://packagist.org/packages/alphasoft-fr/aslinkorm)

## Installation
Use [Composer](https://getcomposer.org/)

### Composer Require
```
composer require alphasoft-fr/aslink-orm
```

## Requirements

* PHP version 8.2

## Introduction

**ASLinkORM** - Manage your SQL data with simplicity and flexibility.

ASLinkORM is an Object-Relational Mapping (ORM) solution designed to address a specific need that has emerged in modern projects. In many cases, projects inherit existing databases that were created without the use of an ORM. However, with the rise of frameworks like Symfony, which advocate for the use of the Doctrine ORM, challenges arose when integrating these pre-existing databases into Symfony projects.

Utilizing the Doctrine ORM for such databases posed difficulties. It involved manually creating numerous entities, mapping columns to entity properties, managing database migrations, and other labor-intensive tasks. Furthermore, this approach imposed constraints and limited flexibility in data management.

To tackle these challenges, we conceptualized ASLinkORM, a bespoke ORM that aims to simplify SQL data management while offering significant flexibility. The goal was to craft an ORM that could seamlessly integrate into existing projects based on Symfony or similar frameworks, without the hassle of re-creating entities or complex migrations.

ASLinkORM stands out with its user-friendliness and its ability to work with existing databases without imposing heavy configuration overhead. It provides a more fluid approach to data mapping, enabling developers to manage relationships between entities and tables intuitively. Moreover, it provides a flexible alternative to database migrations, granting developers control over these processes.

Throughout this guide, we will delve into the intricate features of ASLinkORM, demonstrating how to integrate it into your projects, and how it can streamline your SQL data management while offering the necessary adaptability to cater to your specific needs.

ASLinkORM leverages the powerful capabilities of Doctrine DBAL for efficient SQL data management.
## Getting Started

To get started with ASLinkORM , you need to follow these steps:

1. **Initialize the EntityManager:** In your application's entry point, initialize the `EntityManager` with your database configuration. Make sure to adjust the configuration according to your database setup.

```php
use AlphaSoft\AsLinkOrm\EntityManager;

$config = [
     'url' => 'mysql://username:password@localhost/db_name',
     // ... other options
 ];

 $manager = new EntityManager($config);
```

2. **Create Repositories:** Create repository classes for your models by extending the `Repository` base class. Define the table name, model name, and selectable fields.

```php
use AlphaSoft\AsLinkOrm\Repository\Repository;

class UserRepository extends Repository
{
    public function getEntityName(): string
    {
        return User::class; // Fully qualified name of the model class
    }
    
    // Additional methods for custom queries
}
```

3. **Create Models:** Create model classes by extending the `AsEntity` base class. Define relationships and any custom methods you need.

```php
use AlphaSoft\AsLinkOrm\Entity\AsEntity;

#[Entity(table: "user", repositoryClass: UserRepository::class)]
class User extends AsEntity 
{
    static protected function columnsMapping(): array
    {
        return [
            new \AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn('id'),
            new \AlphaSoft\AsLinkOrm\Mapping\Entity\Column('firstname'),
            new \AlphaSoft\AsLinkOrm\Mapping\Entity\Column('lastname'),
            new \AlphaSoft\AsLinkOrm\Mapping\Entity\Column('email'),
            new \AlphaSoft\AsLinkOrm\Mapping\Entity\Column('password'),
            new \AlphaSoft\AsLinkOrm\Mapping\Entity\Column('isActive', false, 'is_active'),
        ];
    }


    public function getPosts(): \SplObjectStorage
    {
        return $this->hasMany(Post::class, ['user_id' => $this->getPrimaryKeyValue()]);
    }
}
   ```
Or with attributes
```php
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Entity;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;

#[Entity(table: "user", repositoryClass: UserRepository::class)]
#[PrimaryKeyColumn(property: 'id')]
#[Column(property: 'firstname')]
#[Column(property: 'lastname')]
#[Column(property: 'email')]
#[Column(property: 'password')]
#[Column(property: 'isActive', defaultValue: false, name: 'is_active')]
class User extends AsEntity 
{
    public function getPosts(): \SplObjectStorage
    {
        return $this->hasMany(Post::class, ['user_id' => $this->getPrimaryKeyValue()]);
    }
}
   ```
## Accessing Repositories

In ASLinkORM , repositories serve as gateways to access and manipulate data in the underlying database tables. To access a repository, you can use the `DoctrineManager` instance that you've set up. Here's how you can retrieve a repository using the manager:

```php
use AlphaSoft\AsLinkOrm\EntityManager;
use Your\Namespace\Repository\UserRepository;
use Your\Namespace\Entity\User;

// Assuming you have a configured DoctrineManager instance
$manager = new EntityManager(/* configuration options */);

// Retrieving a UserRepository instance
$userRepository = $manager->getRepository(UserRepository::class);
// OR
$userRepository = $manager->getRepository(User::class);
```

In this example, you create a `EntityManager` instance and then use it to retrieve a `UserRepository` instance. You can replace `UserRepository` with the name of any repository class you've defined.


## Basic Operations

Here are some examples of basic operations you can perform using ASLinkORM:

### Inserting Records

```php
use Your\Namespace\User;

$user = new User([
    'firstname' => 'John',
    'lastname' => 'Doe',
    'email' => 'john@example.com',
    'isActive' => true,
]);

$userRepository->insert($user);
```

### Finding Records

```php
$user = $userRepository->findOneBy(['id' => 1]);
```

### Updating Records

```php
$user = $userRepository->findOneBy(['id' => 1]);

$user->set('firstname', 'UpdatedName');
$userRepository->update($user);
```

### Deleting Records

```php
$user = $userRepository->findOneBy(['id' => 1]);

$userRepository->delete($user);
```

### Retrieving Data from Models

In your framework, you can retrieve data from models using various methods. The primary method is the `get` method, which allows you to access an attribute's value by specifying its name.

```php
$user = new User();
$user->set('firstname', 'John');
$firstname = $user->get('firstname'); // Retrieves 'John'
```

If the specified attribute doesn't exist in the model, it will throw an `InvalidArgumentException`.

#### Using `getOrNull` for Safe Retrieval

To safely retrieve data without triggering an exception, you can use the `getOrNull` method, which returns the value of the attribute if it exists or `null` if it doesn't.

```php
$lastname = $user->getOrNull('lastname'); // Retrieves 'null'
```

#### Type-Specific Retrieval

You can also retrieve attribute values with specific data types using dedicated methods. These methods provide type-checking and do not allow for default values when the property is not defined or if the value is of the wrong type.

- `getString` retrieves a string value.

```php
$lastname = $user->getString('lastname', 'Doe'); // Retrieves 'Doe' if 'lastname' exists and is a string
```

- `getInt` retrieves an integer value.

```php
$age = $user->getInt('age', 25); // Retrieves 25 if 'age' exists and is an integer
```

- `getFloat` retrieves a floating-point value.

```php
$price = $product->getFloat('price', 0.0); // Retrieves 0.0 if 'price' exists and is a float
```

- `getBool` retrieves a boolean value.

```php
$isActive = $user->getBool('isActive', false); // Retrieves false if 'isActive' exists and is a boolean
```

- `getArray` retrieves an array.

```php
$tags = $post->getArray('tags', []); // Retrieves an empty array if 'tags' exists and is an array
```

- `getInstanceOf` retrieves an instance of a specified class, or null if it exists and is an instance of the specified class.

```php
$profile = $user->getInstanceOf('profile', Profile::class); // Retrieves an instance of Profile or null if 'profile' exists and is an instance of Profile
```

- `getDateTime` retrieves a `DateTimeInterface` instance, optionally specifying a format for parsing.

```php
$createdAt = $post->getDateTime('created_at', 'Y-m-d H:i:s'); // Retrieves a DateTimeInterface instance or null if 'created_at' exists and is convertible to a valid date
```

Please note that these methods will throw exceptions if the property is not defined or if the value is of the wrong type. If you want to allow default values, you can use the previous examples with default values, but they will not throw exceptions in those cases.

## Relationships

ASLinkORM extends its capabilities by enabling you to define and manage relationships between models. By extending the `HasEntity` class, you can easily establish relationships that allow you to navigate and interact with associated data.

### Defining Relationships

The `HasEntity` class offers two methods, `hasOne` and `hasMany`, which facilitate relationship management. Let's explore these methods using examples:

#### `hasOne` Method

The `hasOne` method establishes a one-to-one relationship between the current model and another related model. Here's an example of how you might use it:

```php
use AlphaSoft\AsLinkOrm\Entity\AsEntity;

#[Entity(table: "user", repositoryClass: UserRepository::class)]
class User extends AsEntity
{
    // ... (other code)
    
    public function getProfile(): ?Profile
    {
        return $this->hasOne(Profile::class, ['user_id' => $this->getPrimaryKeyValue()]);
    }
}
```

In this scenario, the `getProfile` method sets up a one-to-one relationship between the `User` model and the `Profile` model. It returns a single `Profile` instance associated with the user.

#### `hasMany` Method

The `hasMany` method establishes a one-to-many relationship between the current model and another related model. Consider this example:

```php
use AlphaSoft\AsLinkOrm\Entity\AsEntity;

#[Entity(table: "user", repositoryClass: UserRepository::class)]
class User extends AsEntity
{
    // ... (other code)
    
    public function getPosts(): \SplObjectStorage
    {
        return $this->hasMany(Post::class, ['user_id' => $this->getPrimaryKeyValue()]);
    }
}
```

In this illustration, the `getPosts` method sets up a one-to-many relationship between the `User` model and the `Post` model. It returns an `SplObjectStorage` containing all posts associated with the user.

### Navigating Relationships

After defining relationships, you can seamlessly navigate through your data graph. Here's an example of how you can utilize the established relationships to retrieve associated data:

```php
$user = $userRepository->findOneBy(['id' => 1]);

// Retrieve the user's profile
$profile = $user->getProfile();

// Retrieve all posts associated with the user
$posts = $user->getPosts();

foreach ($posts as $post) {
    // Access post attributes
    $title = $post->get('title');
    $content = $post->get('content');
    // ... (other operations)
}
```

By leveraging the `hasOne` and `hasMany` methods, you can efficiently retrieve and manipulate associated data, making your data interactions more intuitive and effective.

## Defining Column Mappings

ASLinkORM provides the flexibility to define column mappings for your models, allowing you to seamlessly manage your data. This can be achieved through the traditional method using the `columnsMapping()` function or the modern attribute-based approach.

### Traditional Approach: `columnsMapping()`

The `Column` object is utilized within the `columnsMapping()` method to establish the relationship between model attributes and corresponding database columns. It enables you to specify default values and optional database column names if they differ from the attribute names in the model.

```php
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;

$column1 = new Column('firstname'); // Basic usage, no specific column name specified
$column2 = new Column('lastname', 'Doe'); // Specifying a default value
$column3 = new Column('email', null, 'user_email'); // Specifying a custom database column name
```

The `columnsMapping()` method serves a dual purpose: it defines the column name mappings for model attributes, specifying which columns to consider during SELECT operations. Additionally, it allows you to set default values for these attributes.

The method should always include the `PrimaryKeyColumn` object, crucial for identifying the unique column used to search for an element in the database. There should be only one `PrimaryKeyColumn` object defined in the `columnsMapping()` method.

```php
static protected function columnsMapping(): array
{
    return [
        new PrimaryKeyColumn('id'),
        new Column('firstname'),
        new Column('lastname'),
        new Column('email'),
        new Column('password'),
        new Column('isActive', false, 'is_active'),
    ];
}
```

### Attribute-Based Approach:

Alternatively, you can use attributes directly above your class properties to define column mappings. This modern approach streamlines the code and enhances readability:

```php
use AlphaSoft\AsLinkOrm\Mapping\Entity\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity\PrimaryKeyColumn;
use AlphaSoft\AsLinkOrm\Mapping\Entity\Entity;

#[PrimaryKeyColumn(property: 'id')]
#[Column(property: 'firstname')]
#[Column(property: 'lastname')]
#[Column(property: 'email')]
#[Column(property: 'password')]
#[Column(property: 'isActive', defaultValue: false, name: 'is_active')]
class User extends AsEntity 
{
    // ... additional class code
}
```

Choose the approach that aligns with your coding preferences and project standards. Both methods achieve the same goal of defining column mappings for your models in ASLinkORM.

In this example, the attribute `isActive` in the `User` model corresponds to the column name `is_active` in the database table. When fetching or inserting data, the ORM will automatically map the attribute names to the appropriate column names using the defined mappings.

For instance, when you insert a new `User` instance:

```php
$user = new User([
    'firstname' => 'John',
    'lastname' => 'Doe',
    'isActive' => true,
]);
$userRepository->insert($user);
```

The columnsMapping() method is essential as it defines the column name mappings for the model's attributes. This mapping is necessary because it specifies how the model's attributes correspond to the columns in the database table. By default, the ORM will attempt to associate each attribute with a column of the same name in the database. However, in cases where the attribute name in the model differs from the corresponding database column name, the Column object can be used to specify custom mappings, as demonstrated in the example: new Column('isActive', false, 'is_active').

## Contributing

If you'd like to contribute to ASLinkORM , feel free to open pull requests and issues on the GitHub repository.

## License

ASLinkORM is open-source software licensed under the MIT License.
