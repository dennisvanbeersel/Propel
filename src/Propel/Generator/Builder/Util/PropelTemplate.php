<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Util;

use Exception;
use Propel\Generator\Exception\InvalidArgumentException;

/**
 * Simple templating system to ease behavior writing
 *
 * @author François Zaninotto
 */
class PropelTemplate
{
    protected ?string $template = null;

    protected ?string $templateFile = null;

    /**
     * Sets a string as a template.
     * The string doesn't need closing php tags.
     *
     * <code>
     * $template->setTemplate('This is <?php echo $name ?>');
     * </code>
     *
     * @param string $template the template string
     *
     * @return void
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * Set a file as a template. The file can be any regular PHP file.
     *
     * <code>
     * $template->setTemplateFile(dirname(__FILE__) . '/template/foo.php');
     * </code>
     *
     * @param string $filePath The (absolute or relative to the include path) file path
     *
     * @return void
     */
    public function setTemplateFile(string $filePath): void
    {
        $this->templateFile = $filePath;
    }

    /**
     * Render the template using the variable provided as arguments.
     *
     * <code>
     * $template = new PropelTemplate();
     * $template->setTemplate('This is <?php echo $name ?>');
     * echo $template->render(array('name' => 'Mike'));
     * // This is Mike
     * </code>
     *
     * @param array $vars An associative array of arguments to be rendered
     *
     * @throws \Propel\Generator\Exception\InvalidArgumentException
     *
     * @return string The rendered template
     */
    public function render(array $vars = []): string
    {
        if ($this->templateFile === null && $this->template === null) {
            throw new InvalidArgumentException('You must set a template or a template file before rendering');
        }

        extract($vars);
        ob_start();

        /**
         * @psalm-suppress InvalidArgument
         * @phpstan-ignore-next-line
         */
        ob_implicit_flush(false);

        /** @var string|null $tempFile */
        $tempFile = null;
        try {
            if ($this->templateFile !== null) {
                require $this->templateFile;
            } else {
                // Use a temp file instead of eval() for security
                $tempFile = tempnam(sys_get_temp_dir(), 'propel_tpl_');
                if ($tempFile === false) {
                    throw new InvalidArgumentException('Unable to create temp file for template rendering');
                }
                $written = file_put_contents($tempFile, '<?php ?>' . $this->template . '<?php ');
                if ($written === false) {
                    unlink($tempFile);

                    throw new InvalidArgumentException('Unable to write template content to temp file');
                }
                require $tempFile;
            }
        } catch (Exception $e) {
            // need to end output buffering before throwing the exception #7596
            ob_end_clean();

            throw $e;
        } finally {
            if (is_string($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return (string)ob_get_clean();
    }
}
