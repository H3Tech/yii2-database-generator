<?php

namespace h3tech\databaseGenerator;

use h3tech\databaseGenerator\models\SchemaGeneratable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * @property boolean $autogenerate
 * @property string $accessToken
 */
class Module extends \yii\base\Module
{
    protected $autogenerate = false;
    protected $modelPaths = [];
    protected $models = [];
    protected $accessToken = null;
    protected static $interface = 'h3tech\databaseGenerator\models\SchemaGeneratable';
    protected static $defaultModelPath = '@app/models';
    protected static $defaultAccessToken = 'sudogeneratedb';
    protected $useDefaultAccessToken = true;

    public function init()
    {
        parent::init();

        if ($this->accessToken === null && $this->useDefaultAccessToken) {
            $this->accessToken = static::$defaultAccessToken;
        }

        if ($this->autoGenerate) {
            $this->generateDatabase();
        }
    }

    public function getAutogenerate()
    {
        return $this->autogenerate;
    }

    public function setAutogenerate($autogenerate)
    {
        if (is_bool($autogenerate)) {
            $this->autogenerate = $autogenerate;
        } else {
            throw new InvalidConfigException("The property 'autogenerate' must be a boolean value");
        }
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        if ($accessToken === false) {
            $this->useDefaultAccessToken = false;
        } elseif (is_string($accessToken) && trim($accessToken) !== '') {
            $this->accessToken = $accessToken;
        } else {
            throw new InvalidConfigException("The property 'accessToken' must be a non-empty string value or false to disable it");
        }
    }

    public function setModelPaths(array $modelPaths)
    {
        $this->modelPaths = $modelPaths;
    }

    protected static function getFiles($folderPath, &$files = [])
    {
        $fileNames = scandir($folderPath);

        foreach ($fileNames as $fileName) {
            $currentPath = realpath($folderPath . DIRECTORY_SEPARATOR . $fileName);

            if (!is_dir($currentPath)) {
                $files[] = $currentPath;
            } elseif ($fileName != "." && $fileName != "..") {
                self::getFiles($currentPath, $files);
            }
        }

        return $files;
    }

    protected static function findModels($modelPath)
    {
        $models = [];

        $folder = Yii::getAlias($modelPath);
        $files = static::getFiles($folder);

        foreach ($files as $file) {
            $nameSpace = ltrim(str_replace('/', '\\', $modelPath) . '\\', '@');
            $className = rtrim(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1), '.php');
            $classIdentifier = $nameSpace . $className;
            if (is_subclass_of($classIdentifier, static::$interface)) {
                $models[] = $classIdentifier;
            }
        }

        return $models;
    }

    public function generateDatabase()
    {
        $updates = [];

        if (empty($this->models)) {
            $modelPaths = empty($this->modelPaths) ? [static::$defaultModelPath] : $this->modelPaths;
            foreach ($modelPaths as $modelPath) {
                $this->models = array_merge($this->models, static::findModels($modelPath));
            }
        }

        /** @var ActiveRecord|SchemaGeneratable $model */
        foreach ($this->models as $model) {
            $db = Yii::$app->db;
            $dbSchema = $db->schema;

            $table = $dbSchema->getRawTableName($model::tableName());
            $fields = $model::fieldTypes();
            $rules = (new $model())->rules();
            $classProperties = get_class_vars($model);

            $columnsByRules = [];
            foreach ($rules as $rule) {
                if (is_array($rule[0])) {
                    foreach ($rule[0] as $column) {
                        if (!array_key_exists($column, $classProperties) && !in_array($column, $columnsByRules)) {
                            $columnsByRules[] = $column;
                        }
                    }
                }
            }

            $missingColumns = array_diff($columnsByRules, array_keys($fields));
            $missingColumnCount = count($missingColumns);
            if ($missingColumnCount > 0) {
                $isMultiple = $missingColumnCount > 1;
                throw new InvalidConfigException("The column" . ($isMultiple ? 's' : '') . " '"
                    . implode("', '", $missingColumns) . "' " . ($isMultiple ? 'are' : 'is')
                    . " missing from the database configuration of " . $model::className());
            }

            if (in_array($table, $dbSchema->tableNames)) {
                $tableSchema = $dbSchema->getTableSchema($table);
                foreach ($fields as $column => $type) {
                    if (!in_array($column, $tableSchema->columnNames)) {
                        $db->createCommand()->addColumn($table, $column, $type)->execute();
                        $updates['fields'][$table][] = $column;
                    }
                }
            } else {
                $db->createCommand()->createTable($table, $fields)->execute();
                $updates['tables'][] = $table;
            }
        }

        return $updates;
    }
}
