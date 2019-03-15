<?= $payloadName ?>:<?= "\n" ?>
  type: relay-mutation-payload<?= "\n" ?>
  config:<?= "\n" ?>
<?php if ($payloadDescription) { ?>
    description: <?= $payloadDescription === '' ? "~\n" : "'$payloadDescription'\n" ?>
<?php } ?>
<?php if ($payloadFields && count($payloadFields) > 0) { ?>
    fields:<?= "\n" ?>
<?php foreach ($payloadFields as $field) { ?>
      <?= $field['name'] ?>:<?= "\n" ?>
        type: '<?= $field['type'] ?><?= ($field['nullable'] === true) ? '' : '!' ?>'<?= "\n" ?>
        description: <?= "'" . $field['description'] . "'" ?? '~' ?><?= "\n" ?>
<?php } ?>
<?php } ?>
