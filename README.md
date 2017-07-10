# Yii2 Database Generator
This extension can automatically update the structure of database tables according to field type definitions in models.

## Installing the extension
The extension can be installed via Composer.

### Adding the repository
Add this repository in your composer.json file, like this:
```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/H3Tech/yii2-database-generator"
    }
],
```
### Adding dependency
Add an entry for the extension in the require section in your composer.json:
```
"h3tech/yii2-database-generator": "dev-master"
```
After this, you can execute `composer update` in your project directory to install the extension.

### Enabling the extension
The extension must be enabled in Yii's web.php by adding an entry for it in the modules section, for example:
```
'modules' => [
    'h3tech-database-generator' => [
        'class' => 'h3tech\databaseGenerator\Module',
    ],
]
```
Then it must be added to the list of bootstrapped items, for example:
```
'bootstrap' => ['log', 'h3tech-database-generator'],
```

## Preparing the models
You can activate the database generating functionality by implementing the `SchemaGeneratable` interface in your model.
So a model's declaration should look something like this:
```
class SomeTestModel extends ActiveRecord implements SchemaGeneratable
```
The `fieldTypes()` function of the interface should return an array which has field names as keys and field types as values, such as:
```
[
    'id' => 'pk',
    'name' => 'string(50) NOT NULL',
    'value' => 'integer NOT NULL',
]
```
You can use Yii's abstract types. You can find the supported types in the [documentation](http://www.yiiframework.com/doc-2.0/yii-db-schema.html) of the Schema class, in the section Constants.

## Usage and settings
If you want the tables to automatically update, you must set the parameter `autogenerate` to `true` in the config array of the module.

By default, the extension doesn't do automatic generation, so you must POST the correct access token to the URL `/h3tech-database-generator/generator/run` via the `access_token` field to update the tables.  
The default access token is `sudogeneratedb`, but can be set to any string via the `accessToken` parameter of the module or turned off completely by setting it to `false`.

An example configuration would be the following:
```
'modules' => [
    'h3tech-database-generator' => [
        'class' => 'h3tech\databaseGenerator\Module',
        'autogenerate' => YII_ENV_DEV || YII_ENV_TEST,
        'accessToken' => '0yppRdbnCkYnidwskUjvvhhv',
    ],
]
```