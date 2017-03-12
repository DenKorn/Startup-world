<?php
/**
 * @var $this yii\web\View
 * @var $requestedId int
 */

$this->title = "Профиль #$requestedId не найден!";
?>

<div class="row">
<div class="col-md-offset-2 col-md-8">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <h3>Профиль пользователя #<?= $requestedId ?> не найден!</h3>
            <p>
                Вы перешли по ошибочной ссылке, либо испытываете форум на прочность.
            </p>
            <p>
                В любом случае такого пользователя у нас в базе нет, так что предлагаем не тратить время, и насладиться
                продуктивным коммюникейшеном в темах форума. Запасайтесь смузи и да пребудет с вами коворкинг!
            </p>
            <a class="btn btn-primary btn-lg" href="https://nikolaev.eda.ua/restorany/chelentano/smuzi-v-assortimente-85067">Запастись смузи</a>
            <a class="btn btn-primary btn-lg" href="<?= \yii\helpers\Url::home(true).'/forum' ?>">Перейти к темам форума</a>
        </div>
    </div>
</div>
</div>