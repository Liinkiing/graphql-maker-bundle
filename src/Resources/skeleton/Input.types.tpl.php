<?= $inputName ?>:<?= "\n" ?>
  type: relay-mutation-input<?= "\n" ?>
  config:<?= "\n" ?>
<?php if ($inputDescription) { ?>
    description: <?= $inputDescription === '' ? "~\n" : "'$inputDescription'\n" ?>
<?php } ?>
<?php if ($inputFields && count($inputFields) > 0) { ?>
    fields:<?= "\n" ?>
<?php foreach ($inputFields as $field) { ?>
      <?= $field['name'] ?>:<?= "\n" ?>
        type: '<?= $field['type'] ?><?= ($field['nullable'] === true) ? '' : '!' ?>'<?= "\n" ?>
        description: <?= "'" . $field['description'] . "'" ?? '~' ?><?= "\n" ?>
<?php } ?>
<?php } ?>
