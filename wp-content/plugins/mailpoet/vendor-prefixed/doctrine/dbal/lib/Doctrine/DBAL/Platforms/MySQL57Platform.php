<?php
 namespace MailPoetVendor\Doctrine\DBAL\Platforms; if (!defined('ABSPATH')) exit; use MailPoetVendor\Doctrine\DBAL\Schema\Index; use MailPoetVendor\Doctrine\DBAL\Schema\TableDiff; use MailPoetVendor\Doctrine\DBAL\Types\Types; class MySQL57Platform extends \MailPoetVendor\Doctrine\DBAL\Platforms\MySqlPlatform { public function hasNativeJsonType() { return \true; } public function getJsonTypeDeclarationSQL(array $column) { return 'JSON'; } protected function getPreAlterTableRenameIndexForeignKeySQL(\MailPoetVendor\Doctrine\DBAL\Schema\TableDiff $diff) { return []; } protected function getPostAlterTableRenameIndexForeignKeySQL(\MailPoetVendor\Doctrine\DBAL\Schema\TableDiff $diff) { return []; } protected function getRenameIndexSQL($oldIndexName, \MailPoetVendor\Doctrine\DBAL\Schema\Index $index, $tableName) { return ['ALTER TABLE ' . $tableName . ' RENAME INDEX ' . $oldIndexName . ' TO ' . $index->getQuotedName($this)]; } protected function getReservedKeywordsClass() { return \MailPoetVendor\Doctrine\DBAL\Platforms\Keywords\MySQL57Keywords::class; } protected function initializeDoctrineTypeMappings() { parent::initializeDoctrineTypeMappings(); $this->doctrineTypeMapping['json'] = \MailPoetVendor\Doctrine\DBAL\Types\Types::JSON; } } 