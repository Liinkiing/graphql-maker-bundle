<?= $name ?>:<?= "\n" ?>
  type: relay-connection
  config:<?= "\n" ?>
<?php if ($nodeType) { ?>
    nodeType: '<?= $nodeType ?><?= ($nodeTypeNullable === true) ? '' : '!' ?>'<?= "\n" ?>
<?php } ?>
    connectionFields:<?= "\n" ?>
      totalCount:<?= "\n" ?>
        type: 'Int!'<?= "\n" ?>
        description: 'Return the number of items of the connection'
<?php if ($hasFields && $fields && count($fields) > 0) { ?>
<?php foreach ($fields as $field) { ?>
      <?= $field['name'] ?>:<?= "\n" ?>
        type: '<?= $field['type'] ?><?= ($field['nullable'] === true) ? '' : '!' ?>'<?= "\n" ?>
        description: <?= "'" . $field['description'] . "'" ?? '~' ?><?= "\n" ?>
<?php } ?>
<?php } ?>
