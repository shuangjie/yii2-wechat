<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%auth}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $source
 * @property string $source_id
 * @property string $union_id
 *
 * @property User $user
 */
class Auth extends ActiveRecord
{
    CONST SOURCE_WECHAT = 'wechat';
    CONST SOURCE_WEAPP = 'weapp';
    public static $source_config = [
        self::SOURCE_WECHAT => '微信公众号',
        self::SOURCE_WEAPP => '微信小程序',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'source', 'source_id'], 'required'],
            [['user_id'], 'integer'],
            [['source', 'source_id', 'union_id'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['source', 'source_id'], 'unique', 'targetAttribute' => ['source', 'source_id'], 'message' => 'The combination of Source and Source ID has already been taken.'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'source' => 'Source',
            'source_id' => 'Source ID',
            'union_id' => 'Union ID',
        ];
    }

    /**
     * @param $user_id
     * @param $source 'weapp' 'wechat'
     * @return array|null|ActiveRecord
     */
    public static function getOpenidByUserId($user_id,$source)
    {
        return self::find()->select('source_id')->where(['user_id' => $user_id,'source' => $source])->one();
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
