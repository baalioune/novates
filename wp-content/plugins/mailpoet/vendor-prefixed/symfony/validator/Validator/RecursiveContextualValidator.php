<?php
 namespace MailPoetVendor\Symfony\Component\Validator\Validator; if (!defined('ABSPATH')) exit; use MailPoetVendor\Symfony\Component\Validator\Constraint; use MailPoetVendor\Symfony\Component\Validator\Constraints\Composite; use MailPoetVendor\Symfony\Component\Validator\Constraints\Existence; use MailPoetVendor\Symfony\Component\Validator\Constraints\GroupSequence; use MailPoetVendor\Symfony\Component\Validator\Constraints\Valid; use MailPoetVendor\Symfony\Component\Validator\ConstraintValidatorFactoryInterface; use MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContext; use MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContextInterface; use MailPoetVendor\Symfony\Component\Validator\Exception\ConstraintDefinitionException; use MailPoetVendor\Symfony\Component\Validator\Exception\NoSuchMetadataException; use MailPoetVendor\Symfony\Component\Validator\Exception\RuntimeException; use MailPoetVendor\Symfony\Component\Validator\Exception\UnexpectedValueException; use MailPoetVendor\Symfony\Component\Validator\Exception\UnsupportedMetadataException; use MailPoetVendor\Symfony\Component\Validator\Exception\ValidatorException; use MailPoetVendor\Symfony\Component\Validator\Mapping\CascadingStrategy; use MailPoetVendor\Symfony\Component\Validator\Mapping\ClassMetadataInterface; use MailPoetVendor\Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface; use MailPoetVendor\Symfony\Component\Validator\Mapping\GenericMetadata; use MailPoetVendor\Symfony\Component\Validator\Mapping\GetterMetadata; use MailPoetVendor\Symfony\Component\Validator\Mapping\MetadataInterface; use MailPoetVendor\Symfony\Component\Validator\Mapping\PropertyMetadataInterface; use MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy; use MailPoetVendor\Symfony\Component\Validator\ObjectInitializerInterface; use MailPoetVendor\Symfony\Component\Validator\Util\PropertyPath; class RecursiveContextualValidator implements \MailPoetVendor\Symfony\Component\Validator\Validator\ContextualValidatorInterface { private $context; private $defaultPropertyPath; private $defaultGroups; private $metadataFactory; private $validatorFactory; private $objectInitializers; public function __construct(\MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContextInterface $context, \MailPoetVendor\Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface $metadataFactory, \MailPoetVendor\Symfony\Component\Validator\ConstraintValidatorFactoryInterface $validatorFactory, array $objectInitializers = []) { $this->context = $context; $this->defaultPropertyPath = $context->getPropertyPath(); $this->defaultGroups = [$context->getGroup() ?: \MailPoetVendor\Symfony\Component\Validator\Constraint::DEFAULT_GROUP]; $this->metadataFactory = $metadataFactory; $this->validatorFactory = $validatorFactory; $this->objectInitializers = $objectInitializers; } public function atPath($path) { $this->defaultPropertyPath = $this->context->getPropertyPath($path); return $this; } public function validate($value, $constraints = null, $groups = null) { $groups = $groups ? $this->normalizeGroups($groups) : $this->defaultGroups; $previousValue = $this->context->getValue(); $previousObject = $this->context->getObject(); $previousMetadata = $this->context->getMetadata(); $previousPath = $this->context->getPropertyPath(); $previousGroup = $this->context->getGroup(); $previousConstraint = null; if ($this->context instanceof \MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContext || \method_exists($this->context, 'getConstraint')) { $previousConstraint = $this->context->getConstraint(); } if (null !== $constraints) { if (!\is_array($constraints)) { $constraints = [$constraints]; } $metadata = new \MailPoetVendor\Symfony\Component\Validator\Mapping\GenericMetadata(); $metadata->addConstraints($constraints); $this->validateGenericNode($value, $previousObject, \is_object($value) ? $this->generateCacheKey($value) : null, $metadata, $this->defaultPropertyPath, $groups, null, \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT, $this->context); $this->context->setNode($previousValue, $previousObject, $previousMetadata, $previousPath); $this->context->setGroup($previousGroup); if (null !== $previousConstraint) { $this->context->setConstraint($previousConstraint); } return $this; } if (\is_object($value)) { $this->validateObject($value, $this->defaultPropertyPath, $groups, \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT, $this->context); $this->context->setNode($previousValue, $previousObject, $previousMetadata, $previousPath); $this->context->setGroup($previousGroup); return $this; } if (\is_array($value)) { $this->validateEachObjectIn($value, $this->defaultPropertyPath, $groups, $this->context); $this->context->setNode($previousValue, $previousObject, $previousMetadata, $previousPath); $this->context->setGroup($previousGroup); return $this; } throw new \MailPoetVendor\Symfony\Component\Validator\Exception\RuntimeException(\sprintf('Cannot validate values of type "%s" automatically. Please provide a constraint.', \gettype($value))); } public function validateProperty($object, $propertyName, $groups = null) { $classMetadata = $this->metadataFactory->getMetadataFor($object); if (!$classMetadata instanceof \MailPoetVendor\Symfony\Component\Validator\Mapping\ClassMetadataInterface) { throw new \MailPoetVendor\Symfony\Component\Validator\Exception\ValidatorException(\sprintf('The metadata factory should return instances of "\\Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface", got: "%s".', \is_object($classMetadata) ? \get_class($classMetadata) : \gettype($classMetadata))); } $propertyMetadatas = $classMetadata->getPropertyMetadata($propertyName); $groups = $groups ? $this->normalizeGroups($groups) : $this->defaultGroups; $cacheKey = $this->generateCacheKey($object); $propertyPath = \MailPoetVendor\Symfony\Component\Validator\Util\PropertyPath::append($this->defaultPropertyPath, $propertyName); $previousValue = $this->context->getValue(); $previousObject = $this->context->getObject(); $previousMetadata = $this->context->getMetadata(); $previousPath = $this->context->getPropertyPath(); $previousGroup = $this->context->getGroup(); foreach ($propertyMetadatas as $propertyMetadata) { $propertyValue = $propertyMetadata->getPropertyValue($object); $this->validateGenericNode($propertyValue, $object, $cacheKey . ':' . \get_class($object) . ':' . $propertyName, $propertyMetadata, $propertyPath, $groups, null, \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT, $this->context); } $this->context->setNode($previousValue, $previousObject, $previousMetadata, $previousPath); $this->context->setGroup($previousGroup); return $this; } public function validatePropertyValue($objectOrClass, $propertyName, $value, $groups = null) { $classMetadata = $this->metadataFactory->getMetadataFor($objectOrClass); if (!$classMetadata instanceof \MailPoetVendor\Symfony\Component\Validator\Mapping\ClassMetadataInterface) { throw new \MailPoetVendor\Symfony\Component\Validator\Exception\ValidatorException(\sprintf('The metadata factory should return instances of "\\Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface", got: "%s".', \is_object($classMetadata) ? \get_class($classMetadata) : \gettype($classMetadata))); } $propertyMetadatas = $classMetadata->getPropertyMetadata($propertyName); $groups = $groups ? $this->normalizeGroups($groups) : $this->defaultGroups; if (\is_object($objectOrClass)) { $object = $objectOrClass; $class = \get_class($object); $cacheKey = $this->generateCacheKey($objectOrClass); $propertyPath = \MailPoetVendor\Symfony\Component\Validator\Util\PropertyPath::append($this->defaultPropertyPath, $propertyName); } else { $object = null; $class = $objectOrClass; $cacheKey = null; $propertyPath = $this->defaultPropertyPath; } $previousValue = $this->context->getValue(); $previousObject = $this->context->getObject(); $previousMetadata = $this->context->getMetadata(); $previousPath = $this->context->getPropertyPath(); $previousGroup = $this->context->getGroup(); foreach ($propertyMetadatas as $propertyMetadata) { $this->validateGenericNode($value, $object, $cacheKey . ':' . $class . ':' . $propertyName, $propertyMetadata, $propertyPath, $groups, null, \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT, $this->context); } $this->context->setNode($previousValue, $previousObject, $previousMetadata, $previousPath); $this->context->setGroup($previousGroup); return $this; } public function getViolations() { return $this->context->getViolations(); } protected function normalizeGroups($groups) { if (\is_array($groups)) { return $groups; } return [$groups]; } private function validateObject($object, string $propertyPath, array $groups, int $traversalStrategy, \MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContextInterface $context) { try { $classMetadata = $this->metadataFactory->getMetadataFor($object); if (!$classMetadata instanceof \MailPoetVendor\Symfony\Component\Validator\Mapping\ClassMetadataInterface) { throw new \MailPoetVendor\Symfony\Component\Validator\Exception\UnsupportedMetadataException(\sprintf('The metadata factory should return instances of "Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface", got: "%s".', \is_object($classMetadata) ? \get_class($classMetadata) : \gettype($classMetadata))); } $this->validateClassNode($object, $this->generateCacheKey($object), $classMetadata, $propertyPath, $groups, null, $traversalStrategy, $context); } catch (\MailPoetVendor\Symfony\Component\Validator\Exception\NoSuchMetadataException $e) { if (!$object instanceof \Traversable) { throw $e; } if (!($traversalStrategy & (\MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT | \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::TRAVERSE))) { throw $e; } $this->validateEachObjectIn($object, $propertyPath, $groups, $context); } } private function validateEachObjectIn(iterable $collection, string $propertyPath, array $groups, \MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContextInterface $context) { foreach ($collection as $key => $value) { if (\is_array($value)) { $this->validateEachObjectIn($value, $propertyPath . '[' . $key . ']', $groups, $context); continue; } if (\is_object($value)) { $this->validateObject($value, $propertyPath . '[' . $key . ']', $groups, \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT, $context); } } } private function validateClassNode($object, ?string $cacheKey, \MailPoetVendor\Symfony\Component\Validator\Mapping\ClassMetadataInterface $metadata, string $propertyPath, array $groups, ?array $cascadedGroups, int $traversalStrategy, \MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContextInterface $context) { $context->setNode($object, $object, $metadata, $propertyPath); if (!$context->isObjectInitialized($cacheKey)) { foreach ($this->objectInitializers as $initializer) { $initializer->initialize($object); } $context->markObjectAsInitialized($cacheKey); } foreach ($groups as $key => $group) { $defaultOverridden = \false; $groupHash = \is_object($group) ? $this->generateCacheKey($group, \true) : $group; if ($context->isGroupValidated($cacheKey, $groupHash)) { unset($groups[$key]); continue; } $context->markGroupAsValidated($cacheKey, $groupHash); if (\MailPoetVendor\Symfony\Component\Validator\Constraint::DEFAULT_GROUP === $group) { if ($metadata->hasGroupSequence()) { $group = $metadata->getGroupSequence(); $defaultOverridden = \true; } elseif ($metadata->isGroupSequenceProvider()) { $group = $object->getGroupSequence(); $defaultOverridden = \true; if (!$group instanceof \MailPoetVendor\Symfony\Component\Validator\Constraints\GroupSequence) { $group = new \MailPoetVendor\Symfony\Component\Validator\Constraints\GroupSequence($group); } } } if ($group instanceof \MailPoetVendor\Symfony\Component\Validator\Constraints\GroupSequence) { $this->stepThroughGroupSequence($object, $object, $cacheKey, $metadata, $propertyPath, $traversalStrategy, $group, $defaultOverridden ? \MailPoetVendor\Symfony\Component\Validator\Constraint::DEFAULT_GROUP : null, $context); unset($groups[$key]); continue; } $this->validateInGroup($object, $cacheKey, $metadata, $group, $context); } if (0 === \count($groups)) { return; } foreach ($metadata->getConstrainedProperties() as $propertyName) { foreach ($metadata->getPropertyMetadata($propertyName) as $propertyMetadata) { if (!$propertyMetadata instanceof \MailPoetVendor\Symfony\Component\Validator\Mapping\PropertyMetadataInterface) { throw new \MailPoetVendor\Symfony\Component\Validator\Exception\UnsupportedMetadataException(\sprintf('The property metadata instances should implement "Symfony\\Component\\Validator\\Mapping\\PropertyMetadataInterface", got: "%s".', \is_object($propertyMetadata) ? \get_class($propertyMetadata) : \gettype($propertyMetadata))); } if ($propertyMetadata instanceof \MailPoetVendor\Symfony\Component\Validator\Mapping\GetterMetadata) { $propertyValue = new \MailPoetVendor\Symfony\Component\Validator\Validator\LazyProperty(static function () use($propertyMetadata, $object) { return $propertyMetadata->getPropertyValue($object); }); } else { $propertyValue = $propertyMetadata->getPropertyValue($object); } $this->validateGenericNode($propertyValue, $object, $cacheKey . ':' . \get_class($object) . ':' . $propertyName, $propertyMetadata, \MailPoetVendor\Symfony\Component\Validator\Util\PropertyPath::append($propertyPath, $propertyName), $groups, $cascadedGroups, \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT, $context); } } if ($traversalStrategy & \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT) { $traversalStrategy = $metadata->getTraversalStrategy(); } if (!($traversalStrategy & (\MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT | \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::TRAVERSE))) { return; } if ($traversalStrategy & \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT && !$object instanceof \Traversable) { return; } if (!$object instanceof \Traversable) { throw new \MailPoetVendor\Symfony\Component\Validator\Exception\ConstraintDefinitionException(\sprintf('Traversal was enabled for "%s", but this class does not implement "\\Traversable".', \get_class($object))); } $this->validateEachObjectIn($object, $propertyPath, $groups, $context); } private function validateGenericNode($value, $object, ?string $cacheKey, ?\MailPoetVendor\Symfony\Component\Validator\Mapping\MetadataInterface $metadata, string $propertyPath, array $groups, ?array $cascadedGroups, int $traversalStrategy, \MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContextInterface $context) { $context->setNode($value, $object, $metadata, $propertyPath); foreach ($groups as $key => $group) { if ($group instanceof \MailPoetVendor\Symfony\Component\Validator\Constraints\GroupSequence) { $this->stepThroughGroupSequence($value, $object, $cacheKey, $metadata, $propertyPath, $traversalStrategy, $group, null, $context); unset($groups[$key]); continue; } $this->validateInGroup($value, $cacheKey, $metadata, $group, $context); } if (0 === \count($groups)) { return; } if (null === $value) { return; } $cascadingStrategy = $metadata->getCascadingStrategy(); if (!($cascadingStrategy & \MailPoetVendor\Symfony\Component\Validator\Mapping\CascadingStrategy::CASCADE)) { return; } if ($traversalStrategy & \MailPoetVendor\Symfony\Component\Validator\Mapping\TraversalStrategy::IMPLICIT) { $traversalStrategy = $metadata->getTraversalStrategy(); } $cascadedGroups = null !== $cascadedGroups && \count($cascadedGroups) > 0 ? $cascadedGroups : $groups; if ($value instanceof \MailPoetVendor\Symfony\Component\Validator\Validator\LazyProperty) { $value = $value->getPropertyValue(); if (null === $value) { return; } } if (\is_array($value)) { $this->validateEachObjectIn($value, $propertyPath, $cascadedGroups, $context); return; } if (!\is_object($value)) { throw new \MailPoetVendor\Symfony\Component\Validator\Exception\NoSuchMetadataException(\sprintf('Cannot create metadata for non-objects. Got: "%s".', \gettype($value))); } $this->validateObject($value, $propertyPath, $cascadedGroups, $traversalStrategy, $context); } private function stepThroughGroupSequence($value, $object, ?string $cacheKey, ?\MailPoetVendor\Symfony\Component\Validator\Mapping\MetadataInterface $metadata, string $propertyPath, int $traversalStrategy, \MailPoetVendor\Symfony\Component\Validator\Constraints\GroupSequence $groupSequence, ?string $cascadedGroup, \MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContextInterface $context) { $violationCount = \count($context->getViolations()); $cascadedGroups = $cascadedGroup ? [$cascadedGroup] : null; foreach ($groupSequence->groups as $groupInSequence) { $groups = (array) $groupInSequence; if ($metadata instanceof \MailPoetVendor\Symfony\Component\Validator\Mapping\ClassMetadataInterface) { $this->validateClassNode($value, $cacheKey, $metadata, $propertyPath, $groups, $cascadedGroups, $traversalStrategy, $context); } else { $this->validateGenericNode($value, $object, $cacheKey, $metadata, $propertyPath, $groups, $cascadedGroups, $traversalStrategy, $context); } if (\count($context->getViolations()) > $violationCount) { break; } } } private function validateInGroup($value, ?string $cacheKey, \MailPoetVendor\Symfony\Component\Validator\Mapping\MetadataInterface $metadata, string $group, \MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContextInterface $context) { $context->setGroup($group); foreach ($metadata->findConstraints($group) as $constraint) { if ($constraint instanceof \MailPoetVendor\Symfony\Component\Validator\Constraints\Existence) { continue; } if (null !== $cacheKey) { $constraintHash = $this->generateCacheKey($constraint, \true); if ($constraint instanceof \MailPoetVendor\Symfony\Component\Validator\Constraints\Composite || $constraint instanceof \MailPoetVendor\Symfony\Component\Validator\Constraints\Valid) { $constraintHash .= $group; } if ($context->isConstraintValidated($cacheKey, $constraintHash)) { continue; } $context->markConstraintAsValidated($cacheKey, $constraintHash); } $context->setConstraint($constraint); $validator = $this->validatorFactory->getInstance($constraint); $validator->initialize($context); if ($value instanceof \MailPoetVendor\Symfony\Component\Validator\Validator\LazyProperty) { $value = $value->getPropertyValue(); } try { $validator->validate($value, $constraint); } catch (\MailPoetVendor\Symfony\Component\Validator\Exception\UnexpectedValueException $e) { $context->buildViolation('This value should be of type {{ type }}.')->setParameter('{{ type }}', $e->getExpectedType())->addViolation(); } } } private function generateCacheKey($object, bool $dependsOnPropertyPath = \false) : string { if ($this->context instanceof \MailPoetVendor\Symfony\Component\Validator\Context\ExecutionContext) { $cacheKey = $this->context->generateCacheKey($object); } else { $cacheKey = \spl_object_hash($object); } if ($dependsOnPropertyPath) { $cacheKey .= $this->context->getPropertyPath(); } return $cacheKey; } } 