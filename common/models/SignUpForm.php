<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class SignUpForm extends Model
{
    public $username;
    public $password;
   // public $password_duplicate;
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return [
            // username rules
            'usernameTrim'     => ['username', 'trim'],
            'usernameLength'   => ['username', 'string', 'min' => 3, 'max' => 255],
            'usernameRequired' => ['username', 'required'],
            /*'usernameUnique'   => [
                'username',
                'unique',
                'targetClass' => 'User',
                'message' => 'This username has already been taken'
            ],*/ //todo добавить позднее валидацию уникальности
            // email rules
            'emailTrim'     => ['email', 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern'  => ['email', 'email'],
            /*'emailUnique'   => [
                'email',
                'unique',
                'targetClass' => User::className(),
                'message' => 'This email address has already been taken'
            ],*/ //todo добавить и тут позднее валидацию
            // password rules
            'passwordRequired' => ['password', 'required'],
            'passwordLength'   => ['password', 'string', 'min' => 6, 'max' => 72],
        ];
    }

    /**
     * проверка, нет ли в базе уже пользователя с таким логином
     *
     * @param $attribute
     * @param $params
     */
    public function validateUsername($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if($attribute == "" || User::findByUsername($attribute)) {
                $this->addError($attribute, 'Пользователь с таким логином уже зарегистрирован!');
            }
        }
    }

    public function formName()
    {
        return 'sign-up-form';
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        //такой юзер может быть зареган ( с таким логином или паролем)

        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Проверка наличия проверяемой почты в базе пользователей
     *
     * @param $attribute
     * @param $params
     */
    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            // TODO!
        }
    }

    /**
     * Регистрация пользователя в базе данных и сразу же аутентификация
     *
     * @return bool whether the user is logged in successfully
     */
    public function signUp()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = new User();
        $this->loadAttributes($user);

        if (!$user->registrate()) {
            return false;
        }

        Yii::$app->session->setFlash(
            'info',
            $this->username.', добро пожаловать на наш форум!'
        );

        return Yii::$app->user->login(User::findByUsername($this->username), 3600 * 24 * 30);

        //return true;
        //todo почистить от лишнего кода

    }

    protected function loadAttributes(User $user)
    {
        $user->setAttributes($this->attributes);
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Логин',
            'email' => 'Адрес e-mail',
            'password' => 'Пароль',
            //'password_duplicate' => 'Пароль (ещё раз)',
        ];
    }

}
