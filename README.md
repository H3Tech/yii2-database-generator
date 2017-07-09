# Yii2 Database Generator
This extension can automatically update the structure of database tables according to field type definitions in models.

## Installation
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
    'database-generator' => [
        'class' => 'h3tech\databaseGenerator\Module',
        'autogenerate' => YII_ENV_DEV || YII_ENV_TEST,
        'accessToken' => '0yppRdbnCkYnidwskUjvvhhv',
    ],
]
```
Then it must be added to the list of bootstrapped items, for example:
```
'bootstrap' => ['log', 'h3tech-database-generator'],
```