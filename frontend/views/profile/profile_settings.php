<?php
/**
 * @var $this yii\web\View
 * @var $isOwnProfile boolean
 * @var $userInfo \common\models\User
 * @var $isAdmin boolean
 */

$PROFILE_CONTROLLER_NAME = 'profileController';
?>

<div class="row col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-6">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php //компоненты первой панели настроек
            echo $this->render('input_field', [
                    'field_caption' => 'Логин',
                    'isOwnProfile' => $isOwnProfile,
                    'placeholder' => 'Изменить свой логин',
                    'value' => $userInfo->username,
                    'help' => 'Изменяйте логин лишь при крайней необходимости.',
                    'initial_class' => 'has-success',
                    'input_id' => 'user_login',
                    'event_listener' => "$PROFILE_CONTROLLER_NAME.updateFieldById('user_login')",
                    'input_type' => 'text',
                    'hide_button' =>false,
            ]);

            if ($userInfo->real_name || ( !$userInfo->real_name && $isOwnProfile)) {
                echo $this->render('input_field', [
                    'field_caption' => 'Имя',
                    'isOwnProfile' => $isOwnProfile,
                    'placeholder' => 'Изменить своё имя',
                    'value' => $userInfo->real_name,
                    'initial_class' => $userInfo->real_name ? 'has-success' : 'has-error',
                    'input_id' => 'user_first_name',
                    'event_listener' => "$PROFILE_CONTROLLER_NAME.updateFieldById('user_first_name')",
                    'input_type' => 'text',
                    'hide_button' =>false,
                ]);
            }

            if ($userInfo->real_surname || ( !$userInfo->real_surname && $isOwnProfile)) {
                echo $this->render('input_field', [
                    'field_caption' => 'Фамилия',
                    'isOwnProfile' => $isOwnProfile,
                    'placeholder' => 'Изменить свою фамилию',
                    'value' => $userInfo->real_surname,
                    'initial_class' => $userInfo->real_surname ? 'has-success' : 'has-error',
                    'input_id' => 'user_second_name',
                    'event_listener' => "$PROFILE_CONTROLLER_NAME.updateFieldById('user_second_name')",
                    'input_type' => 'text',
                    'hide_button' =>false,
                ]);
            }
            ?>
        </div>
    </div>
</div>

<div class="row col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-6">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php //компоненты второй панели настроек

            echo $this->render('input_field', [
                'field_caption' => 'E-mail',
                'isOwnProfile' => $isOwnProfile,
                'placeholder' => 'Изменить привязанную почту',
                'value' => $userInfo->user_mail,
                'initial_class' => $userInfo->user_mail ? 'has-success' : 'has-error',
                'input_id' => 'user_email',
                'event_listener' => "$PROFILE_CONTROLLER_NAME.updateFieldById('user_email')",
                'input_type' => 'email',
                'hide_button' =>false,
            ]);

            if($isOwnProfile) {
                echo $this->render('input_field', [
                    'field_caption' => 'Пароль',
                    'isOwnProfile' => $isOwnProfile,
                    'placeholder' => 'Новый пароль',
                    'value' => '',
                    'initial_class' => '',
                    'input_id' => 'user_password',
                    'event_listener' => "",
                    'input_type' => 'password',
                    'hide_button' =>true,
                ]);

                echo $this->render('input_field', [
                    'field_caption' => '',
                    'isOwnProfile' => $isOwnProfile,
                    'placeholder' => 'Новый пароль (повторно)',
                    'value' => '',
                    'initial_class' => '',
                    'input_id' => 'user_password_repeat',
                    'event_listener' => "$PROFILE_CONTROLLER_NAME.updateFieldById('user_password', 'user_password_repeat')",
                    'input_type' => 'password',
                    'hide_button' =>false,
                ]);
            }
            ?>

            <?php if($isAdmin): //todo обработчик для выбора (через RegistrateJS)?>

            <div class="form-group">
                <label for="select111" class="col-md-2 control-label">Select</label>

                <div class="col-md-10">
                    <select id="select111" class="form-control">
                        <option>обычный пользователь</option>
                        <option>модератор</option>
                        <option>администратор</option>
                    </select>
                </div>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>