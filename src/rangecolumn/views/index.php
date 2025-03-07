<?php
/**
 * @var \yii\web\View $this
 * @var \jinowom\wajaxcrud\rangecolumn\RangeColumnWidget $widget
 */

use yii\helpers\Html;
use jinowom\yii2wtools\tools\JsBlock;

$wid = "RangeColumn{$widget->id}";

$model = $widget->model;
$attribute = $widget->attribute;
$value = $model->$attribute;
if (!is_null($value) && strpos($value, ' - ') !== false ) {
    list($s, $e) = explode(' - ', $value);
}else{
    $s = $e = "";
}
?>

<div id="<?=$wid ?>">
    <div class="dropdown">
        <?=Html::activeInput('text', $model, $attribute, ['class' => "form-control range-v dropdown-toggle", 'placeholder' => "区间", '_id' => "range-v-".$wid, 'data-minV' => $s, 'data-maxV' => $e, 'data-toggle' => "dropdown", 'id' => "rangeB-{$widget->id}", 'aria-haspopup' => true, 'aria-expanded' => true, 'data-stopPropagation' => true, 'readonly' => true,]); ?>
        <div class="dropdown-menu" aria-labelledby="rangeB-<?=$widget->id ?>" style="padding: 0">
            <span class="input-group-addon stopPropagation" _name="min-v" contenteditable="true" style="background: #ffffff"><?=$s ?></span>
            <span class="input-group-addon" pointer-events="none">~</span>
            <span class="input-group-addon stopPropagation" _name="max-v" contenteditable="true" style="background: #ffffff"><?=$e ?></span>
            <span class="input-group-btn">
                <button class="btn btn-primary" type="button" _id="ranger-filter-<?=$wid ?>">确定</button>
            </span>
        </div>
    </div>
</div>

<?php JsBlock::begin(); ?>
<script>
    $(function () {
        function rangeColumnIsNumber(val) {
            let regPos = /^\d+(\.\d+)?$/; //非负浮点数
            let regNeg = /^(-(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*)))$/; //负浮点数
            if(regPos.test(val) || regNeg.test(val)){
                return true;
            }else{
                return false;
            }
        }
        $(document).on('input', "span[_name='min-v']", function (e) {
            let minV = e.target.innerHTML;
            $("#<?=$wid ?>").find("input[_id='range-v-<?=$wid ?>']").attr('data-minV', minV);
        });
        $(document).on('input', "span[_name='max-v']", function (e) {
            let maxV = e.target.innerHTML;
            $("#<?=$wid ?>").find("input[_id='range-v-<?=$wid ?>']").attr('data-maxV', maxV);
        });
        $(document).on('click', "button[_id='ranger-filter-<?=$wid ?>']", function (e) {
            let minV = $(this).parents("#<?=$wid ?>").find("input[_id='range-v-<?=$wid ?>']").attr('data-minV');
            let maxV = $(this).parents("#<?=$wid ?>").find("input[_id='range-v-<?=$wid ?>']").attr('data-maxV');
            if (minV) {
                if (!rangeColumnIsNumber(minV)){
                    alert("最小值必须为数字");
                    return ;
                }
            }
            if (maxV) {
                if (!rangeColumnIsNumber(maxV)){
                    alert("最大值必须为数字");
                    return ;
                }
            }
            if (minV && maxV){
                if (maxV < minV){
                    alert("最大值必须大于最小值");
                    return ;
                }
            }
            let rangeV = "";
            if (minV && !maxV)rangeV = "";
            if (minV && !maxV)rangeV = minV + " - ";
            if (!minV && maxV)rangeV = " - " + maxV;
            if (minV && maxV)rangeV = minV + " - " + maxV;
            let e13=$.Event('keydown');e13.keyCode=13;
            $("#<?=$wid ?>").find("input[_id='range-v-<?=$wid ?>']").val(rangeV).trigger(e13);

        });
    });
</script>
<?php JsBlock::end(); ?>