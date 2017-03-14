<?php
/**
 * Created by PhpStorm.
 * User: Денис Корнейчук
 * Date: 13.03.2017
 * Time: 22:27
 *
 * Форма для рассылки пользователями определенной роли глобальные оповещения
 *
 * @var $this yii\web\View
 */
$this->title = 'Массовая отправка уведомления';
$this->registerJSFile('@web/js/global-alerts-controller.js',['position' => $this::POS_END]);
?>

<form class="form-horizontal">
    <fieldset>
        <legend><?= \yii\helpers\Html::encode($this->title) ?></legend>
        <div class="form-group">
            <label for="messageTextArea" class="col-md-2 control-label">Сообщение рассылки</label>
            <div class="col-md-10">
                <textarea name="message" maxlength="255" class="form-control" rows="3" id="messageTextArea" onkeypress="globalAlertsController.keyPressHandler()"></textarea>
                <span class="help-block">Не забудьте также указать категорию пользователей, которым будет разослано это уведомление. По умолчанию уведомления рассылаются
                лишь обычным пользователям. Пока что длина сообщения ограничена 255 символами, но вскоре мы это исправим!</span>
            </div>
        </div>

        <div class="form-group">
            <label for="selectRole" class="col-md-2 control-label">Кому отправлять</label>

            <div class="col-md-10">
                <select id="selectRole" name="targetRole" class="form-control">
                    <option value="user" selected>Обычным пользователям (не отвлекайте ребят от их стартапов лишний раз)</option>
                    <option value="moderator">Модераторам (не забывайте почаще ругать этих лентяев)</option>
                    <option value="admin">Администраторам (остановись, безумец)</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 0;"> <!-- inline style is just to demo custom css to put checkbox below input above -->
            <div class="col-md-offset-2 col-md-10">
                <div class="checkbox">
                    <label>
                        <input name="mail_only" id="mail_only" type="checkbox"> Только на почту
                        <span class="help-block">Если сообщение слишком длинное или важное, но не срочное, его стоит отправить точно на почту. Такое уведомление никем не будет воспринято нормально</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-10 col-md-offset-2">
                <button type="button" id="formSubmitBtn" class="btn btn-primary">Подтвердить оповещение</button>
            </div>
        </div>
    </fieldset>
</form>
