<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */
?>
export class <?= $className ?> {
  constructor(
<?php foreach ($properties as $property => $data): ?>
<?php
$newProperty = preg_replace_callback('%_([a-z0-9_])%i', function ($matches) {
    return ucfirst($matches[1]);
}, $property);
?>
    // <?= ($data['comment'] ? ' ' . strtr($data['comment'], ["\n" => ' ']) : '')  .  "\n" ?>
    public <?= "{$newProperty}: ". ($data['type'] == 'int' ? 'number' : $data['type']) . ",\n" ?>
<?php endforeach; ?>
  ) {}
}
