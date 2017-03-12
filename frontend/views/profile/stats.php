<?php
/**
 * @var $isOwnProfile boolean
 * @var $stats array
 */
?>

<div class="row ">
    <div class="col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading text-center">
                <h4>
                    <?= $isOwnProfile ? 'Моя статистика' : 'Статистика пользователя' ?>
                </h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped table-hover">
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td>Написал сообщений</td>
                        <td><?= $stats['created_messages_count'] ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Создал тем</td>
                        <td><?= $stats['created_themes_count'] ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Мой средний рейтинг</td>
                        <td><?= $stats['average_user_rating'] ?></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Оценил сообщений</td>
                        <td><?= $stats['user_rates_count'] ?></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Рейтинг моих оценок</td>
                        <td><?= $stats['summary_user_rate'] ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>