<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Model;

use Propel\Generator\Config\GeneratorConfigInterface;
use Propel\Generator\Exception\BuildException;
use Propel\Generator\Util\BehaviorLocator;

/**
 * BehaviorableTrait use it on every model that can hold behaviors
 */
trait BehaviorableTrait
{
    /**
     * @var array<\Propel\Generator\Model\Behavior>
     */
    protected array $behaviors = [];

    private ?BehaviorLocator $behaviorLocator = null;

    /**
     * @return \Propel\Generator\Config\GeneratorConfigInterface|null
     */
    abstract protected function getGeneratorConfig(): ?GeneratorConfigInterface;

    /**
     * Returns the behavior locator.
     *
     * @return \Propel\Generator\Util\BehaviorLocator
     */
    private function getBehaviorLocator(): BehaviorLocator
    {
        if ($this->behaviorLocator === null) {
            $config = $this->getGeneratorConfig();
            if ($config !== null) {
                $this->behaviorLocator = $config->getBehaviorLocator();
            } else {
                $this->behaviorLocator = new BehaviorLocator();
            }
        }

        return $this->behaviorLocator;
    }

    /**
     * Adds a new Behavior
     *
     * @param \Propel\Generator\Model\Behavior|array $bdata
     *
     * @throws \Propel\Generator\Exception\BuildException when the added behavior is not an instance of \Propel\Generator\Model\Behavior
     *
     * @return \Propel\Generator\Model\Behavior
     */
    public function addBehavior($bdata): Behavior
    {
        if ($bdata instanceof Behavior) {
            $behavior = $bdata;

            // the new behavior is already registered
            if ($this->hasBehavior($behavior->getId()) && $behavior->allowMultiple()) {
                // the user probably just forgot to specify the "id" attribute
                if ($behavior->getId() === $behavior->getName()) {
                    throw new BuildException(sprintf('Behavior "%s" is already registered. Specify a different ID attribute to register the same behavior several times.', $behavior->getName()));
                }

                // or he copy-pasted it and forgot to update it.
                throw new BuildException(sprintf('A behavior with ID "%s" is already registered.', $behavior->getId()));
            }

            $this->registerBehavior($behavior);
            $this->behaviors[$behavior->getId()] = $behavior;

            return $behavior;
        }

        // Behaviors removed in Propel 3.0 (umbrella spec §6.1). Schemas in
        // the wild may still reference these — surface a clear error pointing
        // at the migration guide rather than a generic class-not-found.
        $removedBehaviors = [
            'validate' => 'Validate behavior was removed in Propel 3.0 (it imported Symfony 3.0-removed classes and produced dead generated code). Use Symfony Validator on application DTOs instead. See docs/MIGRATION-FROM-PRE-AI.md.',
            'query_cache' => 'QueryCache behavior was removed in Propel 3.0 (it depended on apc_*, removed from PHP in 5.5/2013). Use a PSR-6/PSR-16 cache at the application layer instead. See docs/MIGRATION-FROM-PRE-AI.md.',
        ];
        if (isset($removedBehaviors[$bdata['name']])) {
            throw new BuildException($removedBehaviors[$bdata['name']]);
        }

        $locator = $this->getBehaviorLocator();
        $class = $locator->getBehavior($bdata['name']);
        $behavior = new $class();
        if (!($behavior instanceof Behavior)) {
            throw new BuildException(sprintf(
                'Behavior [%s: %s] not instance of %s',
                $bdata['name'],
                $class,
                '\Propel\Generator\Model\Behavior',
            ));
        }
        $behavior->loadMapping($bdata);

        return $this->addBehavior($behavior);
    }

    /**
     * @param \Propel\Generator\Model\Behavior $behavior
     *
     * @return void
     */
    abstract protected function registerBehavior(Behavior $behavior): void;

    /**
     * Returns the list of behaviors.
     *
     * @return array<\Propel\Generator\Model\Behavior>
     */
    public function getBehaviors(): array
    {
        return $this->behaviors;
    }

    /**
     * check if the given behavior exists
     *
     * @param string $id the behavior id
     *
     * @return bool True if the behavior exists
     */
    public function hasBehavior(string $id): bool
    {
        return isset($this->behaviors[$id]);
    }

    /**
     * Get behavior by id
     *
     * @param string $id the behavior id
     *
     * @return \Propel\Generator\Model\Behavior|null A behavior object or null if the behavior doesn't exist
     */
    public function getBehavior(string $id): ?Behavior
    {
        if ($this->hasBehavior($id)) {
            return $this->behaviors[$id];
        }

        return null;
    }
}
