<?= $name ?>:
  type: <?= "$type\n" ?>
<?php if ($inherits && count($inherits) > 0) { ?>
  inherits: [<?= implode(', ', $inherits) ?>]
<?php } ?>
  config:
<?php if ($interfaces && count($interfaces) > 0) { ?>
    interfaces: [<?= implode(', ', $interfaces) ?>]
<?php } ?>
    description: A super description
    fields:
      title:
        type: String!
        description: An inspiring title
