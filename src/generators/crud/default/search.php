<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;
use jinowom\yii2wtools\tools\ArrayHelper;


/* @var $this yii\web\View */
/* @var $generator \jinowom\wajaxcrud\generators\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$editableFields = ArrayHelper::str2arr($generator->editableFields);
$dateRangeFields = ArrayHelper::str2arr($generator->dateRangeFields);
$rangeFields = ArrayHelper::str2arr($generator->rangeFields);
$thumbImageFields = ArrayHelper::str2arr($generator->thumbImageFields);
$enumFields = ArrayHelper::str2arr($generator->enumFields);
foreach ($editableFields as $k => $v) {
    $editableFields[$k] = "'{$v}'";
}
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

$pks = $generator->modelClass::primaryKey();
if ($generator->isDesc){
    $defaultSort = "SORT_DESC";
}else{
    $defaultSort = "SORT_ASC";
}
if (count($pks) === 1) {
    $pk = $pks[0];
    $defaultOrder = "['$pk' => $defaultSort, ]";
} else {
    $defaultOrder = "[";
    foreach ($pks as $pk) {
        $defaultOrder .= "'$pk' => $defaultSort, ";
    }
    $defaultOrder .= "]";
}

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use jinowom\wajaxcrud\generators\crud\SpecialFilterTrait;
use jinowom\yii2wtools\tools\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use jinowom\yii2wtools\tools\ArrayHelper;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;

/**
 * <?= $searchModelClass ?> represents the model behind the search form about `<?= $generator->modelClass ?>`.
 */
class <?= $searchModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?>

{
    use SpecialFilterTrait;

    const SCENARIO_EDITABLE = 'editable';

    public function rules()
    {
        return [
            <?= implode(",\n            ", $rules) ?>,
        ];
    }

    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            self::SCENARIO_EDITABLE => [<?=implode(',', $editableFields) ?>],
        ]);
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();
        $this->load($params);
        <?= implode("        ", $searchConditions) ?>
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            <?php if($generator->isDesc): ?>'sort' => ['defaultOrder' => <?=$defaultOrder ?>],
            <?php else: ?>'sort' => ['defaultOrder' => <?=$defaultOrder ?>],
        <?php endif; ?>]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        return $dataProvider;
    }
}
