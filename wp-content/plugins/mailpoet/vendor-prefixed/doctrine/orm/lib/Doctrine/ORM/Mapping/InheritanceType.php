<?php
 namespace MailPoetVendor\Doctrine\ORM\Mapping; if (!defined('ABSPATH')) exit; use Attribute; use MailPoetVendor\Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor; final class InheritanceType implements \MailPoetVendor\Doctrine\ORM\Mapping\Annotation { public $value; public function __construct(string $value) { $this->value = $value; } } 