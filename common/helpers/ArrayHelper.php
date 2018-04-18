<?php
namespace common\helpers;

use yii\base\Arrayable;
use yii\base\Exception;

class ArrayHelper extends \yii\helpers\ArrayHelper {

    public static function gettype($obj){
        if (is_object($obj)){
            return 'object';
        }
        if (is_array($obj)){
            return 'array';
        }
        if (is_int($obj)){
            return 'integer';
        }
        else{
            new Exception('unsupported object type');
        }
    }
    public static function parseMixArray($mixArr){
        $arr = [];
        $map = [];
        foreach ($mixArr as $k=>$v){
            if (!is_int($k) && is_string($k) && !empty($k)){
                $map[$k] = $v;
                $arr[] = $k;
            }
            else{
                $arr[] = $v;
            }
        }
        return [$arr, $map];
    }

    public static function rename($sourceList, $rename){
        $targetList = [];
        $multiLine = true;
        // 判断是否是二维数组, 并将一维数组转换为二维数组
        if ( self::isMultiDim($sourceList) ){
            $sourceList = [$sourceList];
            $multiLine = false;
        }
        foreach ($sourceList as $rawData){
            $target = [];
            foreach ($rawData as $k=>$v){
                if (array_key_exists($k, $rename)){
                    $k = $rename[$k];
                }
                $target[$k] = $v;
            }
            $targetList[] = $target;
        }
        return $targetList;
    }
    /**
     * 从对象中抽取字段
     * @param $source {Array|List}  抽取对象来源
     * @param $fields {Array|String} 需要抽取的字段 数组或以逗号分隔的字符串: 'id, msg, title'(字段之间可以有空格)
     * @param bool $exclude 是否反向抽取，$fields中的字段将不会被抽取
     * @return array
     */
    public static function extract($sourceList, $fields, $exclude=false){
        if (count($sourceList) == 0){
            return $sourceList;
        }
        $rename = [];
        if (is_array($fields)){
            list($fields, $rename) = self::parseMixArray($fields);
        }
        elseif (is_string($fields)){
            $fields = explode(',', str_replace(' ', '', $fields));
        }
        $targetList = [];
        $multiLine = true;
        // 判断是否是二维数组, 并将一维数组转换为二维数组
        if ( self::isMultiDim($sourceList) ){
            $sourceList = [$sourceList];
            $multiLine = false;
        }
        foreach ($sourceList as $source){
            $source = self::toArray($source);
            $target = call_user_func($exclude ? 'array_diff_key' : 'array_intersect_key', $source, array_flip($fields));
            $targetList[] = $target;
        }
        $targetList = count($rename) > 0 ? self::rename($targetList, $rename) : $targetList;
        return $multiLine ? $targetList : $targetList[0];
    }
    public static function extractColumn($sourceList, $columns){
        if (count($sourceList) == 0){
            return $sourceList;
        }
        if (is_string($columns)){
            $columns = explode(',', str_replace(' ', '', $columns));
        }
        $multiLine = true;
        // 判断是否是二维数组, 并将一维数组转换为二维数组
        if ( self::isMultiDim($sourceList) ){
            $sourceList = [$sourceList];
            $multiLine = false;
        }
        $targetList = [];
        foreach ($sourceList as $source){
            $target = [];
            foreach ($columns as $column){
                $target[] = $source[$column];
            }
            $targetList[] = $target;
        }
        if (count($columns) == 1){
            $targetList = call_user_func_array('array_merge', $targetList);
        }
        return $multiLine ? $targetList : $targetList[0];
    }
    /**
     * 字段重命名
     * 默认只对$rules中的字段做映射，其它字段会原样返回
     * @param $sourceList {Array|List} 需要重命名的对象或列表
     * @param $rules {Array} 重命名规则
     * @param array $excludeList {Array|String} 处在列表中的字段将不会返回
     * @return array|mixed
     */
    public static function remap($sourceList, $rules, $excludeList=null){
        if (count($sourceList) == 0){
            return $sourceList;
        }
        if ( $excludeList != null){
            if (is_string($excludeList)){
                $excludeList = explode(',', str_replace(' ', '', $excludeList));
            }
        }
        $targetList = [];
        $multiLine = true;
        // 判断是否是二维数组, 并将一维数组转换为二维数组
        if ( self::isMultiDim($sourceList)){
            $sourceList = [$sourceList];
            $multiLine = false;
        }
        $ruleKeys = array_keys($rules);
        $extra = array_fill_keys($ruleKeys, null);
        foreach ($sourceList as $source){
            $source = array_merge($extra, self::toArray($source));
            $target = [];
            foreach ($source as $key=>$value){
                if ($excludeList != null && in_array($key, $excludeList)){
                    continue;
                }
                $target[ in_array($key, $ruleKeys) ? $rules[$key] : $key] = $value;
            }
            $targetList[] = $target;
        }
        return $multiLine ? $targetList : $targetList[0];
    }

    /**
     * 将对象列表转换为 哈希表
     * @param $dataList
     * @param $keyField
     * @return array
     */
    public static function toHash($dataList, $keyField){
        $map = [];
        foreach ($dataList as $index => $data){
            $key = is_callable($keyField) ? call_user_func($keyField, $data, $index, $dataList) : $data[$keyField];
            $map[$key] = $data;
        }
        return $map;
    }

    /**
     * 判断数组是否是多为数组
     * @param $array 需要判断的数组
     * @param int $dimension 数组维数
     * @return bool
     * @throws \Error
     */
    public static function isMultiDim($array, $dimension=1){
        if ($dimension < 1){
            throw new \Error('dimension must greater then one');
        }
        return count($array, $dimension - 1) == count($array, $dimension);
    }
}