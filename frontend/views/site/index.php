<?php
use yii\helpers\Html;
$this->title = 'Главная';
?>

<div class="jumbotron">
    <h2 class="text-center">Добро пожаловать на форум успешных людей!</h2>
    <p>Форумы подобного формата едва можно пересчитать на пальцах рук. Здесь вы можете рассказать о своих лучших практиках по организации
        прорывного бизнеса, либо задать вопросы о бизнесе, которые ранее было попросту некому задать!</p>
    <?= (Yii::$app->user->id) ? "" : "<p>Регистрация и использование форума <strong>абсолютно бесплатны</strong>, в ответ мы просим вас лишь вести информативную беседу, делая этот форум
            более полезным и интересным!</p>
       <p class='text-center'>
        <a class=\"btn btn-primary btn-lg\" href=\"site/sign-up\">Зарегистрироваться</a>
       </p>" ?>

    <?= file_get_contents('http://loripsum.net/api/6/medium/prude') ?>

    <p>Проект был создан в рамках курсовой работы и работы для личного портфолио. Сайт разработан на Yii2 и лежит в открытом доступе.</p>

    <p class="text-center">
        <a class="btn btn-primary btn-lg" href="https://github.com/DenKorn/Startup-world">Этот проект на GitHub</a>
        <a class="btn btn-primary btn-lg" href="https://vk.com/den_korn">Связаться с автором</a>
    </p>
</div>
