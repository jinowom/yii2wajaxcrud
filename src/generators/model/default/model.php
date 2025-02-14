<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator \jinowom\wajaxcrud\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

use jinowom\yii2wtools\tools\ArrayHelper;
use jinowom\yii2wtools\tools\Tools;

$_enumData = [];
if ($enumData = $generator->enumData) {
    $enumDataArr = ArrayHelper::str2arr($enumData, ";");
    foreach ($enumDataArr as $k => $v) {
        list($name, $kv) = ArrayHelper::str2arr($v, ":");
        $kvArr = ArrayHelper::str2arr($kv, "|");
        $_kvArr = [];
        foreach ($kvArr as $k1 => $v1) {
            $item = ArrayHelper::str2arr($v1);
            if (!isset($item[2])) $item[] = $item[0];
            $_kvArr[] = $item;
        }
        $_enumData[$name] = $_kvArr;
    }
}

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
use jinowom\yii2wtools\tools\ArrayHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
<?php if ($generator->useSoftDelete): ?>
use jinowom\yii2wsoftdelete\SoftDeleteTrait;
use jinowom\yii2wsoftdelete\SoftDeleteBehavior;
<?php endif; ?>

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
 * @author
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
<?php foreach ($_enumData as $name => $kvArr): ?>
 * @property-read array $<?=$name ?>Datas
 * @property-read array $<?=$name ?>Desc
<?php endforeach; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->extendModelClass, '\\') . "\n" ?>
{
    const SCENARIO_TEST = 'test';
<?php foreach ($_enumData as $name => $kvArr): ?>

<?php foreach ($kvArr as $k => $item): $constName = strtoupper(Tools::uncamelize(ucfirst($name).ucfirst($item[2]))); $constNameVal = is_numeric($item[0])?$item[0]:"'{$item[0]}'"; ?>
    const <?=$constName; ?> = <?=$constNameVal; ?>;
<?php endforeach; ?>

    /**
     * @return array [desc]
     */
    public static function get<?= ucfirst($name) ?>Datas()
    {
        return [
<?php foreach ($kvArr as $k => $item): $constName = strtoupper(Tools::uncamelize(ucfirst($name).ucfirst($item[2]))); $constNameVal = is_numeric($item[1])?$item[1]:"'{$item[1]}'"; ?>
            self::<?=$constName ?> => [<?=$constNameVal ?>],
<?php endforeach; ?>
        ];
    }

    /**
     * @return array
     */
    public static function get<?= ucfirst($name) ?>Desc()
    {
        $descArr = [];
        foreach (static::get<?= ucfirst($name) ?>Datas() as $k => $v) {
            $descArr[$k] = $v[0];
        }
        return $descArr;
    }
<?php endforeach; ?>

<?php if ($generator->useSoftDelete): ?>
    use SoftDeleteTrait;

    public static function getDeletedAtAttribute()
    {
        return "deleted_at";
    }
<?php endif; ?>

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge($behaviors, [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
                'updatedAtAttribute' => false,
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => false,
                'updatedByAttribute' => false,
            ],
<?php if ($generator->useSoftDelete): ?>
            'soft-delete' => [
                'class' => SoftDeleteBehavior::class,
                'deletedAtAttribute' => static::getDeletedAtAttribute(),
            ],
<?php endif; ?>
        ]);
        return $behaviors;
    }

    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels = ArrayHelper::merge($attributeLabels, []);
        return $attributeLabels;
    }

    public function rules()
    {
        $rules = parent::rules();
        /*foreach ($rules as $k => $v) {
            if ($v[1] == 'required'){
                $rules[$k][0] = array_diff($rules[$k][0], ['created_at', 'updated_at', 'created_by', 'updated_by']);
            }
        }*/
        $rules = ArrayHelper::merge($rules, [
//            [[], 'required', 'on' => self::SCENARIO_TEST],
        ]);
        return $rules;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios = ArrayHelper::merge($scenarios, [
            self::SCENARIO_TEST => [],
        ]);
        return $scenarios;
    }

<?php if ($generator->useSoftDelete): ?>
    public static function getOrSetLastUpdatedAt($updatedAt = 0)
    {
        if ($updatedAt > 0) {
            Yii::$app->cache->delete("<?=$generator->db ?>-<?=$className ?>-getOrSetLastUpdatedAt");
        }
        return Yii::$app->cache->getOrSet("<?=$generator->db ?>-<?=$className ?>-getOrSetLastUpdatedAt", function () use ($updatedAt) {
            if ($updatedAt > 0) {
                return $updatedAt;
            }
            $lastModel = static::findWithTrashed()->orderBy(['updated_at' => SORT_DESC])->one();
            return $lastModel ? $lastModel->updated_at : 0;
        });
    }
<?php endif; ?>

    /**
     * @return false|mixed|<?=$className ?>[]
     */
    public static function getAllWithCache()
    {
        return Yii::$app->cache->getOrSet("<?=$generator->db ?>-<?=$className ?>-getAllWithCache", function () {
            return static::find()->all();
        }, null);
    }

    /**
     * @param $id
     * @return false|<?=$className ?>|mixed
     */
    public static function getByIdWithCache($id)
    {
        return Yii::$app->cache->getOrSet("<?=$generator->db ?>-<?=$className ?>-getByIdWithCache/{$id}", function () use ($id) {
            return static::findOne($id);
        }, null);
    }

    public function deleteCaches()
    {
        Yii::$app->cache->delete("<?=$generator->db ?>-<?=$className ?>-getAllWithCache");
        Yii::$app->cache->delete("<?=$generator->db ?>-<?=$className ?>-getByIdWithCache/{$this->id}");
<?php if ($generator->useSoftDelete): ?>
        static::getOrSetLastUpdatedAt(time());
<?php endif; ?>
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->deleteCaches();
    }

    public function afterRefresh()
    {
        parent::afterRefresh();
        $this->deleteCaches();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->deleteCaches();
    }

<?php if ($generator->useSoftDelete): ?>
    public function afterRestore()
    {
        $this->deleteCaches();
    }
<?php endif; ?>

    # relations
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * {@inheritdoc}
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
    # static funs

    public function test()
    {
        $test = self::instance();
        $test->setScenario(self::SCENARIO_TEST);
        $test->save();
        var_dump($test->toArray());
    }

    # funs
}
