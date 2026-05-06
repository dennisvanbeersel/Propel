<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Om;

use Propel\Generator\Exception\BuildException;
use Propel\Generator\Model\Inheritance;

/**
 * Generates the empty stub query class for use with single table inheritance.
 *
 * This class produces the empty stub class that can be customized with
 * application business logic, custom behavior, etc.
 *
 * @author François Zaninotto
 */
class ExtensionQueryInheritanceBuilder extends AbstractOMBuilder
{
    /**
     * The current child "object" we are operating on.
     */
    protected ?Inheritance $child = null;

    /**
     * Returns the name of the current class being built.
     *
     * @return string
     */
    #[\Override]
    public function getUnprefixedClassName(): string
    {
        return $this->getChild()->getClassName() . 'Query';
    }

    /**
     * Gets the package for the [base] object classes.
     *
     * @return string|null
     */
    #[\Override]
    public function getPackage(): ?string
    {
        return ($this->getChild()->getPackage() ?: parent::getPackage());
    }

    /**
     * Set the child object that we're operating on currently.
     *
     * @param \Propel\Generator\Model\Inheritance $child Inheritance
     *
     * @return void
     */
    public function setChild(Inheritance $child): void
    {
        $this->child = $child;
    }

    /**
     * Returns the child object we're operating on currently.
     *
     * @throws \Propel\Generator\Exception\BuildException
     *
     * @return \Propel\Generator\Model\Inheritance
     */
    public function getChild(): Inheritance
    {
        if (!$this->child) {
            throw new BuildException('The ExtensionQueryInheritanceBuilder needs to be told which child class to build (via setChild() method) before it can build the stub class.');
        }

        return $this->child;
    }

    /**
     * Adds class phpdoc comment and opening of class.
     *
     * @param string $script The script will be modified in this method.
     *
     * @return void
     */
    #[\Override]
    protected function addClassOpen(string &$script): void
    {
        $table = $this->getTable();
        $baseBuilder = $this->getNewQueryInheritanceBuilder($this->getChild());
        $baseClassName = $this->getClassNameFromBuilder($baseBuilder);

        $script .= "\n";
        $this->addClassLevelComment(
            $script,
            sprintf("Skeleton subclass for representing a query for one of the subclasses of the '%s' table.", $table->getName()),
        );
        $script .= "
class " . $this->getUnqualifiedClassName() . ' extends ' . $baseClassName . "
{
";
    }

    /**
     * Specifies the methods that are added as part of the stub object class.
     *
     * By default there are no methods for the empty stub classes; override this method
     * if you want to change that behavior.
     *
     * @see ObjectBuilder::addClassBody()
     *
     * @param string $script
     *
     * @return void
     */
    #[\Override]
    protected function addClassBody(string &$script): void
    {
    }

    /**
     * Closes class.
     *
     * @param string $script
     *
     * @return void
     */
    #[\Override]
    protected function addClassClose(string &$script): void
    {
        $script .= "
}
";
    }
}
