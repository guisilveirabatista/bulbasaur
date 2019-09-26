<?php

namespace app\models;

use Yii;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property string $id
 * @property string $uid
 * @property string $username
 * @property string $email
 * @property string $password
 * @property int $status
 * @property int $contact_email
 * @property int $contact_phone
 * @property string $created
 * @property string $updated
 * @property string $auth_key
 *
 * @property Message[] $messages
 * @property Message[] $messages0
 * @property PhoneNumber[] $phoneNumbers
 * @property Trip[] $trips
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{

    const STATUS_INSERTED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BLOCKED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'auth_key'], 'required'],
            [['status', 'contact_email', 'contact_phone'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['email'], 'email'],
            [['password'], 'string', 'max' => 60],
            [['username'], 'string', 'max' => 45],
            [['auth_key'], 'string', 'max' => 60],
            [['email'], 'string', 'max' => 255],
            [['uid'], 'unique'],
            [['email'], 'unique'],
            [['auth_key'], 'unique'],
        ];
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->setUid();
            $this->setAuthKey();
        }
        return parent::beforeValidate();
    }

    private function setUid()
    {
        $this->uid = Yii::$app->getSecurity()->generatePasswordHash(date('YmdHis') . rand(1, 999999));
    }

    private function setAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString(60);
    }

    public function activate()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->setUid();
        return $this->save();
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
        }
        $this->updated = new Expression('NOW()');
        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uid' => Yii::t('app', 'Uid'),
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'status' => Yii::t('app', 'Status'),
            'contact_email' => Yii::t('app', 'Contact Email'),
            'contact_phone' => Yii::t('app', 'Contact Phone'),
            'created' => Yii::t('app', 'Created'),
            'updated' => Yii::t('app', 'Updated'),
            'auth_key' => Yii::t('app', 'Auth Key'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['from_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages0()
    {
        return $this->hasMany(Message::className(), ['to_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoneNumbers()
    {
        return $this->hasMany(PhoneNumber::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrips()
    {
        return $this->hasMany(Trip::className(), ['user_id' => 'id']);
    }

    /** INCLUDE USER LOGIN VALIDATION FUNCTIONS**/
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    /* modified */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by password reset token
     *
     * @param  string      $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        $expire = \Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
        ]);
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
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    //public function validatePassword($password)
    //{
    //    return $this->password === sha1($password);
    //    //return $this->password === md5($password);
    //}

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
        // $this->password_hash = md5($password);
        //$this->password = md5($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Security::generateRandomKey();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Security::generateRandomKey() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

}
