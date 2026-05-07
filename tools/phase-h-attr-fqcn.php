<?php

declare(strict_types=1);

/**
 * One-shot Phase H.1.2 follow-up: convert #[AsCommand(...)] to FQCN form
 * #[\Symfony\Component\Console\Attribute\AsCommand(...)] required by the
 * Spryker AttributesSniff. Removes the `use` import for AsCommand.
 */

$root = dirname(__DIR__);
$dir = $root . '/src/Propel/Generator/Command';

foreach (glob($dir . '/*Command.php') as $path) {
    $src = (string)file_get_contents($path);
    if (strpos($src, '#[AsCommand(') === false) {
        continue;
    }
    // Replace the attribute usage with FQCN.
    $src = str_replace('#[AsCommand(', '#[\\Symfony\\Component\\Console\\Attribute\\AsCommand(', $src);
    // Remove the now-unused use line.
    $src = preg_replace("/^use Symfony\\\\Component\\\\Console\\\\Attribute\\\\AsCommand;\\n/m", '', $src);
    file_put_contents($path, $src);
    echo basename($path) . ": done\n";
}
