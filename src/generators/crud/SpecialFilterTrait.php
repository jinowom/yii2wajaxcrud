<?php

namespace jinowom\wajaxcrud\generators\crud;

use jinowom\yii2wtools\tools\ArrayHelper;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

trait SpecialFilterTrait
{
    public static $EMPTY_STRING = "(空字符)";
    public static $NO_EMPTY = "(非空)";
    public static $NO_SET = "(未设置)";
    public static $NEEDLE = " - ";

    /**
     * 区间查询
     * @param ActiveQuery $query
     * @param string $attribute
     * @param boolean $isDate
     */
    public function rangeFilter(&$query, $attribute, $isDate = false)
    {
        $needle = static::$NEEDLE;
        $value = $this->$attribute;
        if ( ! is_null($value) && strpos($value, $needle) !== false ) {
            list($s, $e) = explode($needle, $value);
            if ($isDate){
                $s = strtotime($s);
                $e = strtotime($e);
            }
            if ($s)$query->andFilterWhere(['>=', $attribute, $s]);
            if ($e)$query->andFilterWhere(['<=', $attribute, $e]);
        }
    }

    /**
     * 特殊值查询
     * @param ActiveQuery $query
     * @param string $attribute
     * @param string $filter_type
     */
    public function fieldFilter(&$query, $attribute, $filter_type = "=")
    {
        $value = $this->$attribute;
        $value = trim($value);
        switch($value){
            case self::$NO_SET:
                $query->andFilterWhere(['IS', $attribute, new Expression('NULL')]);
                break;
            case self::$EMPTY_STRING:
                $query->andFilterWhere([$attribute => '']);
                break;
            case self::$NO_EMPTY:
                $query->andFilterWhere(['IS NOT', $attribute, new Expression('NULL')])->andWhere(['<>', $attribute, '']);
                break;
            default:
                $query->andFilterWhere([$filter_type, $attribute, $value]);
                break;
        }
    }
}