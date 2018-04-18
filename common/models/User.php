<?php
namespace common\models;

use common\helpers\redis\RedisClient;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $nickname
 * @property integer $sex
 * @property string $city
 * @property string $province
 * @property string $country
 * @property string $language
 * @property string $avatar
 * @property string $source
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 *
 * @property Auth[] $auths
 * @property Auth $wechatAuth
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    static $status_config = [
        self::STATUS_DELETED => '不可用',
        self::STATUS_ACTIVE => '正常',
    ];

    CONST SOURCE_DEFAULT = '';
    const SOURCE_WECHAT = 'wechat';
    const SOURCE_WEAPP = 'weapp';
    static $source_config = [
        self::SOURCE_DEFAULT => '未知',
        self::SOURCE_WECHAT => '微信',
        self::SOURCE_WEAPP => '微信小程序',
    ];

    //性别
    const SEX_UNKNOWN = 0;
    const SEX_MALE = 1;
    const SEX_FEMALE = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
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
            [['username','nickname'], 'required'],
            [['username', 'nickname', 'email'], 'string', 'max' => 255],
            //[['username'], 'unique'],
            //[['username'], 'match', 'pattern'=>'/^[a-z]\w*$/i'],
            //[['email'], 'unique'],
            ['email', 'default', 'value' => ''],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            //['role', 'default', 'value' => self::ROLE_USER],
            // ['auth_key', 'default', 'value' => self::AUTH_KEY],
            //['role', 'in', 'range' => [self::ROLE_USER]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'nickname' => '昵称',
            'sex' => '性别',
            'city' => '城市',
            'province' => '省份',
            'country' => '国家',
            'language' => '语言',
            'avatar' => '头像',
            'source' => '用户注册来源',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $redis = RedisClient::getRedisClient();
        $userInfo = $redis->get($token);
        if(!$userInfo){
            return null;
        }
        $userInfo = json_decode($userInfo, true);
        return static::findIdentity($userInfo['id']);
//        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates access token
     * @param string $prefix 前缀
     * @return string
     */
    public function generateAccessToken($prefix = "")
    {
        return $prefix.Yii::$app->security->generateRandomString();
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuths()
    {
        return $this->hasMany(Auth::className(), ['user_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWechatAuth(){
        return $this->hasOne(Auth::className(), ['user_id' => 'id'])->onCondition(['source' => self::SOURCE_WECHAT]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWeappAuth(){
        return $this->hasOne(Auth::className(), ['user_id' => 'id'])->onCondition(['source' => self::SOURCE_WEAPP]);
    }


}
