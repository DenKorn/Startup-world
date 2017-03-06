<?php
use frontend\assets\DiscussionAsset;
DiscussionAsset::register($this);
$this->title = 'Форум - '.$discussionTitle;
?>
<div class="message-user">@<?= $discussionInitiatorUsername ?></div>
<div class="message-created-time"><?= $rootMsgModel->created_at ?></div>
<h4><strong>Тема:</strong> <?= $discussionTitle ?></h4>
<p style="margin:0"><strong>Содержание:</strong> <?= $rootMsgModel->content ?></p>
<?php
if($clientModel) { ?>
<a onclick="messagingController.prepareModal(<?= $rootMsgModel->msg_id ?>)" class="btn btn-default btn-xs" data-toggle="modal" data-target="#myModal">Ответить</a>
<?php } ?>
<?php
//todo добавить подключение кнопки редактирования и удаления
?>

<div class="messages-common-container"></div>

<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title">Ответить на сообщение</h5>
            </div>
            <div class="modal-body">
                <textarea class="form-control" rows="6" id="respond-message"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default send-btn" data-dismiss="modal" onclick="messagingController.sendModal()">Отправить</button>
                <button type="button" class="btn btn-default close-btn" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<?= (Yii::$app->user->isGuest) ? "<div class=\"sign-advice-container\">
        <p class=\"sign-advice-title\">Войдя, либо зарегистрировавшись, вы сможете принять участие в этой беседе.</p>
        <p><a class=\"btn btn-primary btn-lg\" href='site\login'>Вход</a></p>
        <p><a class=\"btn btn-primary btn-lg\" href='site\sign-up'>Регистрация</a></p>
</div>" : "";
?>

<?php
$script = <<< JS
window.ROOT_MSG_ID = $rootMsgModel->msg_id;

function firstDiscussionLoad(attempt) {
    if(typeof messagingController.expandBranch === 'function') {
     messagingController.expandBranch(window.ROOT_MSG_ID);
    } else if(attempt <= 4) {
            console.log("Attempt №"+attempt+" to load first message by function 'expandBranch' failed (беда, сэр, основной скрипт для переписки не успел загрузиться "+attempt+"й раз!).")
            setTimeout(function(){firstDiscussionLoad(attempt+1)},100); 
        }
}

firstDiscussionLoad(1);
JS;
$this->registerJs($script);
?>
