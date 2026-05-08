<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Command;

use Propel\Generator\Manager\ReverseManager;
use Propel\Generator\Schema\Dumper\XmlDumper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'database:reverse', description: 'Reverse-engineer a XML schema file based on given database. Uses given `connection` as name, as dsn or your `reverse.connection` configuration in propel config as connection.', aliases: ['reverse'])]
class DatabaseReverseCommand extends AbstractCommand
{
    /**
     * @var string
     */
    public const DEFAULT_OUTPUT_DIRECTORY = 'generated-reversed-database';

    /**
     * @var string
     */
    public const DEFAULT_DATABASE_NAME = 'default';

    /**
     * @var string
     */
    public const DEFAULT_SCHEMA_NAME = 'schema';

    /**
     * @var string
     */
    public const REVERSE_FORMAT_INFORMATION_SCHEMA = 'information-schema';

    /**
     * @inheritDoc
     */
    #[\Override]
    protected function configure()
    {
        parent::configure();

        $this
            ->addOption('output-dir', null, InputOption::VALUE_REQUIRED, 'The output directory', self::DEFAULT_OUTPUT_DIRECTORY)
            ->addOption('database-name', null, InputOption::VALUE_REQUIRED, 'The database name used in the created schema.xml. If not defined we use `connection`.')
            ->addOption('schema-name', null, InputOption::VALUE_REQUIRED, 'The schema name to generate', self::DEFAULT_SCHEMA_NAME)
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'The PHP namespace to use for generated models')
            ->addOption(
                'reverse-format',
                null,
                InputOption::VALUE_REQUIRED,
                'Reverse-engineering strategy: information-schema (INFORMATION_SCHEMA / pg_catalog queries). '
                . 'The legacy `legacy-show-create` SHOW CREATE TABLE regex parser was removed in Propel 4.0.',
                self::REVERSE_FORMAT_INFORMATION_SCHEMA,
            )
            ->addArgument(
                'connection',
                InputArgument::OPTIONAL,
                'Connection name or dsn to use. Example: \'mysql:host=127.0.0.1;dbname=test;user=root;password=foobar\' (don\'t forget the quote for dsn)',
                'default',
            );
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configOptions = [];

        // Phase G.2.7 (Propel 4.0): only `information-schema` is now a valid
        // --reverse-format. The legacy `legacy-show-create` branch and its
        // REVERSE_FORMAT_LEGACY_SHOW_CREATE constant were removed.
        $reverseFormat = (string)$input->getOption('reverse-format');
        if ($reverseFormat !== self::REVERSE_FORMAT_INFORMATION_SCHEMA) {
            $output->writeln(sprintf(
                '<error>Invalid --reverse-format value "%s". Only "%s" is supported in Propel 4.0; '
                . 'the legacy `legacy-show-create` branch was removed. See UPGRADE-4.0.md.</error>',
                $reverseFormat,
                self::REVERSE_FORMAT_INFORMATION_SCHEMA,
            ));

            return static::CODE_ERROR;
        }

        $connection = (string)$input->getArgument('connection');
        if (strpos($connection, ':') === false) {
            //treat it as connection name
            $configOptions['propel']['reverse']['connection'] = $connection;
            if (!$input->getOption('database-name')) {
                $input->setOption('database-name', $connection);
            }
        } else {
            //probably a dsn
            $configOptions += $this->connectionToProperties('reverseconnection=' . $connection, 'reverse');
            $configOptions['propel']['reverse']['parserClass'] = sprintf(
                '\\Propel\\Generator\\Reverse\\%sSchemaParser',
                ucfirst($configOptions['propel']['database']['connections']['reverseconnection']['adapter']),
            );

            if (!$input->getOption('database-name')) {
                $input->setOption('database-name', self::DEFAULT_DATABASE_NAME);
            }
        }
        $generatorConfig = $this->getGeneratorConfig($configOptions, $input);

        $this->createDirectory($input->getOption('output-dir'));

        $manager = new ReverseManager(new XmlDumper());
        $manager->setGeneratorConfig($generatorConfig);
        $manager->setLoggerClosure(function ($message) use ($input, $output): void {
            if ($input->getOption('verbose')) {
                $output->writeln($message);
            }
        });
        $manager->setWorkingDirectory($input->getOption('output-dir'));
        $manager->setDatabaseName($input->getOption('database-name'));
        $manager->setSchemaName($input->getOption('schema-name'));

        $namespace = $input->getOption('namespace');

        if ($namespace) {
            $manager->setNamespace($namespace);
        }

        if ($manager->reverse() === true) {
            $output->writeln('<info>Schema reverse engineering finished.</info>');
        }

        return static::CODE_SUCCESS;
    }
}
