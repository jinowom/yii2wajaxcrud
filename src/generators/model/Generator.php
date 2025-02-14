<?php
/**
 * Created by PhpStorm.
 * Date: 19-5-9
 * Time: 下午4:28
 */

namespace jinowom\wajaxcrud\generators\model;


use EasyWeChat\Kernel\Support\Arr;
use jinowom\yii2wtools\tools\ArrayHelper;
use yii\gii\CodeFile;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\helpers\Inflector;

class Generator extends \yii\gii\generators\model\Generator
{
    public $showName = 'Jinowom MODEL GENERATOR';

    public $baseNs = 'common\models\db\tables';
    public $baseModelClass;
    public $extendModelClass;
    public $ns = 'common\models\db';
    public $queryNs = 'common\models\db\queries';
    public $useSoftDelete = true;
    public $enumData = 'status:10,正常,active|-1,禁用,disable;type:1,默认';
    public $useTablePrefix = true;
    public $generateLabelsFromComments = true;
    public $enableI18N = true;

    public function getName()
    {
        return $this->showName;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['baseNs', 'baseModelClass', 'enumData'], 'filter', 'filter' => 'trim'],
            [['baseNs'], 'filter', 'filter' => function ($value) { return trim($value, '\\'); }],

            [['baseNs', 'baseModelClass'], 'required'],
            [['baseModelClass'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['baseNs'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['baseNs'], 'validateNamespace'],
            [['baseModelClass'], 'validateBaseModelClass', 'skipOnEmpty' => false],
            [['enumData'], 'validateEnumData'],
            [['useSoftDelete'], 'boolean'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'baseNs' => 'base Namespace',
            'baseModelClass' => 'Base Model Class Name',
        ]);
    }

    public function validateBaseModelClass()
    {
        if ($this->isReservedKeyword($this->baseModelClass)) {
            $this->addError('baseModelClass', 'Class name cannot be a reserved PHP keyword.');
        }
        if ((empty($this->tableName) || substr_compare($this->tableName, '*', -1, 1)) && $this->baseModelClass == '') {
            $this->addError('baseModelClass', 'Base Model Class cannot be blank if table name does not end with asterisk.');
        }
    }

    public function validateEnumData($attribute, $params)
    {
        if ($enumData = $this->$attribute) {
            $enumDataArr = ArrayHelper::str2arr($enumData, ";");
            foreach ($enumDataArr as $k => $v) {
                list($name, $kv) = ArrayHelper::str2arr($v, ":");
                if (!$kv) {
                    $this->addError($attribute, "格式错误");
                    break;
                }
                $kvArr = ArrayHelper::str2arr($kv, "|");
                foreach ($kvArr as $k1 => $v1) {
                    $item = ArrayHelper::str2arr($v1);
                    if (!in_array(count($item), [2, 3])) {
                        $this->addError($attribute, "{$kv} format error");
                    }
                    $itemKey = $item[2]??$item[0];
                    if (!preg_match('/^[\w\_]+$/', $itemKey)) {
                        $this->addError($attribute, "item key {$itemKey} format error");
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return ['baseModel.php', 'model.php'];
    }

    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            $modelClassName = $this->generateClassName($tableName);
            $queryClassName = ($this->generateQuery) ? $this->generateQueryClassName($modelClassName) : false;
            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName,
                'className' => $modelClassName,
                'queryClassName' => $queryClassName,
                'tableSchema' => $tableSchema,
                'properties' => $this->generateProperties($tableSchema),
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => isset($relations[$tableName]) ? $relations[$tableName] : [],
            ];
            // BaseModel :
            $files[] = new CodeFile(
                \Yii::getAlias('@' . str_replace('\\', '/', $this->baseNs)) . '/' . $modelClassName . '.php',
                $this->render('baseModel.php', $params)
            );
            // Model :
            $this->extendModelClass = $this->baseNs.'\\'.$this->baseModelClass;
            $files[] = new CodeFile(
                \Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $modelClassName . '.php',
                $this->render('model.php', $params)
            );
            // query :
            if ($queryClassName) {
                $params['className'] = $queryClassName;
                $params['modelClassName'] = $modelClassName;
                $files[] = new CodeFile(
                    \Yii::getAlias('@' . str_replace('\\', '/', $this->queryNs)) . '/' . $queryClassName . '.php',
                    $this->render('query.php', $params)
                );
            }
        }

        return $files;
    }

    public function generateRules($table)
    {
        $types = [];
        $lengths = [];
        $defaults = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            $types['trim'][] = $column->name;
            if ($column->allowNull){
                if ($column->defaultValue === null){
                    $defaultValue = 'null';
                    $defaults[$defaultValue][] = $column->name;
                }else{
                    $defaultValue = $column->defaultValue;
                    $defaultValue = is_numeric($defaultValue)?$defaultValue:"'{$defaultValue}'";
                    $defaults[$defaultValue][] = $column->name;
                }
            }else{
                if ($column->defaultValue === null){
                    $types['required'][] = $column->name;
                }else{
                    $defaultValue = $column->defaultValue;
                    $defaultValue = is_numeric($defaultValue)?$defaultValue:"'{$defaultValue}'";
                    $defaults[$defaultValue][] = $column->name;
                }
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_TINYINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                case Schema::TYPE_JSON:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
                    break;
            }
        }
        $rules = [];
        $driverName = $this->getDbDriverName();
        foreach ($types as $type => $columns) {
            $defaultValue = $column->defaultValue?:null;
            if ($driverName === 'pgsql' && $type === 'integer') {
                $rules[] = "[['" . implode("', '", $columns) . "'], 'default', 'value' => {$defaultValue}]";
            }
            $rules[] = "[['" . implode("', '", $columns) . "'], '{$type}']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => {$length}]";
        }
        foreach ($defaults as $defaultValue => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'default', 'value' => {$defaultValue}]";
        }
        $db = $this->getDbConnection();
        // Unique indexes rules
        try {
            $uniqueIndexes = array_merge($db->getSchema()->findUniqueIndexes($table), [$table->primaryKey]);
            $uniqueIndexes = array_unique($uniqueIndexes, SORT_REGULAR);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList']]";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        // Exist rules for foreign keys
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);
            unset($refs[0]);
            $attributes = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            $rules[] = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }
}