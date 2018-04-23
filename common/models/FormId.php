<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%form_id}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $form_id
 * @property integer $type
 * @property integer $times
 * @property integer $created_at
 * @property integer $updated_at
 */
class FormId extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form_id}}';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'times', 'created_at', 'updated_at'], 'integer'],
            [['form_id'], 'string', 'max' => 50],
            [['user_id'], 'string', 'max' => 11],
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
            'form_id' => 'Form ID',
            'type' => 'Type',
            'times' => 'Times',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @param $user_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getFormId($user_id) {
        return self::find()->select('form_id')
            ->where(['user_id' => $user_id])
            ->andWhere(['>','times',0])
            ->one();
    }

    /**
     * @param $form_id
     */
    public static function usedFormId($form_id) {
        $formid =  self::findOne(['form_id' => $form_id]);
        $formid->times -= 1;
        $formid->save();
    }

    /**
     * @param $user_id
     * @param $form_id
     * @param string $type
     * @return FormId
     */
    public static function saveFormId($user_id,$form_id,$type = 'form') {
        $form = new FormId();
        $form->user_id = $user_id;
        $form->form_id = $form_id;
        $form->type = $type == 'form' ? 1 : 2;
        $form->times = $type == 'form' ? 1 : 3;
        $form->save();
        return $form;
    }
}
