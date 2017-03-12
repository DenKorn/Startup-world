<?php
/**
 * @var $isBanned boolean
 * @var $ban_reason string
 */
?>

<div class="row">
<div class="col-md-offset-2 col-md-8 col-lg-offset-0 col-lg-12">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <h1 style="display: inline"><div style="display:inline-block; width:3.3%"></div>УПС!</h1>
            <br>
            <p style="font-size: 1.3em;">Похоже, тебя заблокировал один из модераторов или администраторов.
                Тебе недоступно участие в переписке, оценка сообщений, а также создание новых тем.
                Вскоре блокировка снимется, впредь веди себя на форуме хорошо! :)</p>
            <?php if($isBanned): ?>
            <p style="font-size: 1.3em">Причина блокировки: <?= $ban_reason?> </p>
            <?php endif; ?>
            <p style="font-size: 1.3em;">А пока что, займись чем-нибудь полезным. Например, зацени самые трендовые проекты на Kikstarter.
                Отечественному краудфандингу есть много чему у него поучиться.</p>

            <a class="btn btn-primary btn-lg" href="https://www.kickstarter.com/discover/popular?ref=discovery_overlay">Перейти на Kikstarter</a>
        </div>
    </div>
</div>
</div>