<?php
/**
 * @var $field_caption string
 * @var $placeholder string
 * @var $value string
 * @var $help string
 * @var $input_id string
 * @var $isOwnProfile boolean
 * @var $initial_class string
 * @var $hide_button boolean
 * @var $input_type string
 * @var $event_listener string
 */
?>

<div class="form-group label-floating <?= $initial_class && $isOwnProfile ? $initial_class : "" ?>" id="<?= $input_id ?>">
    <div class="input-group">
        <span class="input-group-addon"><?= $field_caption ?></span>
        <?= $placeholder && $isOwnProfile ? "<label class=\"control-label\" for=\"addon3a\">$placeholder</label>" : "" ?>
        <input type="<?= $input_type ? $input_type : 'text' ?>"
               class="form-control" <?= $value ? "value=$value" : "" ?> <?= $isOwnProfile ? "" : "disabled" ?>>
        <?= isset($help) ? "<p class=\"help-block\">$help</p>" : "" ?>
        <?= $isOwnProfile && !$hide_button? "<span class=\"input-group-btn\">
      <button type=\"button\" class=\"btn btn-fab btn-fab-mini\" onclick=\"$event_listener\">
        <i class=\"material-icons\">done</i>
      </button>
    </span>" : "" ?>
    </div>
</div>
