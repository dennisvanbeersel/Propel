<?php

declare(strict_types=1);

/**
 * One-shot Phase H.1.2 helper: apply #[AsCommand] attributes to the
 * remaining 12 console commands and strip their legacy setName/
 * setAliases/setDescription calls. Idempotent.
 */

$commands = [
    'GraphvizGenerateCommand' => ['name' => 'graphviz:generate', 'description' => 'Generate Graphviz files (.dot)', 'aliases' => ['graphviz']],
    'InitCommand' => ['name' => 'init', 'description' => 'Initializes a new project', 'aliases' => []],
    'MigrationCreateCommand' => ['name' => 'migration:create', 'description' => 'Create an empty migration class', 'aliases' => []],
    'MigrationDiffCommand' => ['name' => 'migration:diff', 'description' => 'Generate diff classes', 'aliases' => ['diff']],
    'MigrationDownCommand' => ['name' => 'migration:down', 'description' => 'Execute migrations down', 'aliases' => ['down']],
    'MigrationMigrateCommand' => ['name' => 'migration:migrate', 'description' => 'Execute all pending migrations', 'aliases' => ['migrate']],
    'MigrationStatusCommand' => ['name' => 'migration:status', 'description' => 'Get migration status', 'aliases' => ['status']],
    'MigrationUpCommand' => ['name' => 'migration:up', 'description' => 'Execute migrations up', 'aliases' => ['up']],
    'ModelBuildCommand' => ['name' => 'model:build', 'description' => 'Build the model classes based on Propel XML schemas', 'aliases' => ['build']],
    'SqlBuildCommand' => ['name' => 'sql:build', 'description' => 'Build SQL files', 'aliases' => ['build-sql']],
    'SqlInsertCommand' => ['name' => 'sql:insert', 'description' => 'Insert SQL statements', 'aliases' => ['insert-sql']],
    'TestPrepareCommand' => ['name' => 'test:prepare', 'description' => 'Prepare the Propel test suite by building fixtures', 'aliases' => []],
];

$root = dirname(__DIR__);

foreach ($commands as $cls => $meta) {
    $path = $root . "/src/Propel/Generator/Command/$cls.php";
    $src = (string)file_get_contents($path);

    // Insert use Symfony\Component\Console\Attribute\AsCommand if missing.
    if (strpos($src, 'use Symfony\\Component\\Console\\Attribute\\AsCommand;') === false) {
        $useAttr = "use Symfony\\Component\\Console\\Attribute\\AsCommand;\n";
        if (preg_match('/^use Symfony\\\\Component\\\\Console\\\\[^;]+;$/m', $src, $m, PREG_OFFSET_CAPTURE)) {
            $insertOffset = (int)$m[0][1];
            $src = substr($src, 0, $insertOffset) . $useAttr . substr($src, $insertOffset);
        }
    }

    // Build attribute string.
    $aliasesArg = $meta['aliases'] === [] ? '' : ", aliases: ['" . implode("', '", $meta['aliases']) . "']";
    $attribute = "#[AsCommand(name: '{$meta['name']}', description: '{$meta['description']}'{$aliasesArg})]";

    // Insert attribute above the class declaration if not already present.
    if (strpos($src, '#[AsCommand') === false) {
        $src = (string)preg_replace(
            "/^class\\s+$cls\\s+extends\\s+AbstractCommand/m",
            "$attribute\nclass $cls extends AbstractCommand",
            $src,
            1,
        );
    }

    // Strip legacy setName / setAliases / setDescription chained calls.
    $src = (string)preg_replace("/\\s*->setName\\(\\s*'[^']+'\\s*\\)/", '', $src);
    $src = (string)preg_replace("/\\s*->setAliases\\(\\s*\\[[^\\]]+\\]\\s*\\)/", '', $src);
    $src = (string)preg_replace("/\\s*->setDescription\\(\\s*'[^']+'\\s*\\)/", '', $src);

    file_put_contents($path, $src);
    echo "$cls: done\n";
}
