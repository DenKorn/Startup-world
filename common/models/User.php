<?php
namespace common\models;

use DateTime;
use DateTimeZone;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $real_name
 * @property string $real_surname
 * @property string $user_mail
 * @property string $role
 * @property string $last_activity
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $auth_key
 * @property integer $status
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public function actualizeOnlineStatus()
    {
        $this->last_activity = new Expression('NOW()');
        $this->save();
    }

    /**
     * Проверяет, находится ли пользователь на форуме в данный момент.
     * Использует: время последней активности пользователя, текущее время, настройки уведомлений (допустимое время для статуса "онлайн" +  разброс)
     */
    public function isOnline()
    {
        $notificationSettings = GeneralSettings::getSettingsObjByName('USER_NOTIFICATIONS');
        $TIME_CONFIG = GeneralSettings::getSettingsObjByName('TIME');
        $maxOnlinePeriod = $notificationSettings->online_interval_in_seconds + $notificationSettings->online_interval_dispersion/2;

        $time_now = new DateTime(null, new DateTimeZone($TIME_CONFIG->server_timezone));
        $lastActivity = new DateTime($this->last_activity);
        $interval = ($time_now->getTimestamp() - $lastActivity->getTimestamp()); //абсолютная разность времени в секундах
        return $interval < $maxOnlinePeriod+1000;
    }

    /**
     * Возвращает список id пользователей, по имени их роли. Метод создан, как более оптимальное решение, для подготовки перед
     * массовой рассылой в админ-панели.
     *
     * @param $roleName
     * @return array
     */
    public static function getUserIdListByRoleName($roleName)
    {
        return Yii::$app->db->createCommand("SELECT user_id as id FROM auth_assignment WHERE item_name = '$roleName'")->queryAll();
    }

    /**
     * Регистрация пользователя
     */
    public function registrate()
    {

        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        try {
            if (!$this->save()) {
                return false;
            }

            //$this->mailer->sendWelcomeMessage($this, isset($token) ? $token : null); TODO сделать отсылку письма
            Yii::$app->authManager->assign(Yii::$app->authManager->getRole('user'), $this->id);

            return true;
        } catch (\Exception $e) {
            \Yii::warning($e->getMessage());
            throw $e;
        }
    }

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
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
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
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function setAttributes($values, $safeOnly = true)
    {
        $this->username = $values['username'];
        $this->setPassword($values['password']);
        $this->user_mail = $values['email'];
        $this->generateAuthKey();
        //todo добавить проверку корректности сохранения
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
}
