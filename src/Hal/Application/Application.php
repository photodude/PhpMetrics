<?php
namespace Hal\Application;

use Hal\Application\Config\ConfigException;
use Hal\Application\Config\Parser;
use Hal\Application\Config\Validator;
use Hal\Component\File\Finder;
use Hal\Report\Html\Reporter;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;


class Application
{

    /**
     * @param $argv
     */
    public function run($argv)
    {
        // formatter
        $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, new OutputFormatter());

        // config
        $config = (new Parser())->parse($argv);
        $config->set('output-html', './log');
        try {
            (new Validator())->validate($config);
        } catch (ConfigException $e) {
            $output->writeln(sprintf("\n<error>%s</error>\n", $e->getMessage()));
            $output->writeln((new Validator())->help());
            exit(1);
        }

        // find files
        $finder = new Finder();
        $files = $finder->fetch($config->get('files'));

        // analyze
        $metrics = (new Analyze($output))->run($files);

        // report
        (new Reporter($config, $output))->generate($metrics);

        // end
        $output->writeln('');
        $output->writeln('<info>Done</info>');
    }
}