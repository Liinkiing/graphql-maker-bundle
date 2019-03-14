<?= $name ?>:<?= "\n" ?>
  type: <?= "$type\n" ?>
<?php if ($inherits && count($inherits) > 0) { ?>
  inherits: [<?= implode(', ', array_map(function ($item) { return "'$item'"; }, $inherits)) ?>]<?= "\n" ?>
<?php } ?>
  config:<?= "\n" ?>
<?php if ($interfaces && count($interfaces) > 0) { ?>
    interfaces: [<?= implode(', ', array_map(function ($item) { return "'$item'"; }, $interfaces)) ?>]<?= "\n" ?>
<?php } ?>
<?php if ($description) { ?>
    description: <?= $description === '' ? "~\n" : "'$description'\n" ?>
<?php } ?>
<?php if ($hasFields && $fields && count($fields) > 0) { ?>
    fields:<?= "\n" ?>
<?php foreach ($fields as $field) { ?>
      <?= $field['name'] ?>:<?= "\n" ?>
        type: '<?= $field['type'] ?><?= ($field['nullable'] === true) ? '' : '!' ?>'<?= "\n" ?>
        description: <?= "'" . $field['description'] . "'" ?? '~' ?><?= "\n" ?>
<?php } ?>
<?php } ?>
