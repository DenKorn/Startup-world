<?php
/**
 * @var $showedForModerator boolean
 */
?>

<div class="row">
<div class="col-md-offset-2 col-md-8 col-lg-offset-0 col-lg-12">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <h3 style="display: inline"><div style="display:inline-block; width:3.3%"></div>Этот пользователь заблокирован</h3>
            <br>
            <?php if(! $showedForModerator): ?> <span style="font-size: 1.3em;">Информация недоступна.</span> <?php endif; ?>
        </div>
    </div>
</div>
</div>