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
<?php if (!$fields) { ?>
    fields:<?= "\n" ?>
      id:<?= "\n" ?>
        type: 'ID!'<?= "\n" ?>
        description: 'The <?= $name ?> id'<?= "\n" ?>
<?php } ?>
<?php if ($fields) { ?>
    fields:<?= "\n" ?>
<?php } ?>
