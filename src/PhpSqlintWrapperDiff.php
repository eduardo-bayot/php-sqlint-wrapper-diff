<?php

namespace PhpSqlintWrapperDiff;

use League\CLImate\CLImate;
use PhpSqlintWrapperDiff\Filter\Exception\FilterException;
use PhpSqlintWrapperDiff\Filter\Filter;
use PhpSqlintWrapperDiff\Filter\Rule\SqlFileRule;

class PhpSqlintWrapperDiff
{

    const EXEC = 'sqlint';

    /**
     * @var array
     */
    protected $argv = [];

    /**
     * @var CLImate
     */
    protected $climate;

    /**
     * @var bool
     */
    protected $isVerbose = false;

    /**
     * @var int
     */
    protected $exitCode = 0;

    /**
     * @var string
     */
    protected $baseBranch;

    /**
     * @var string
     */
    protected $currentBranch = '';

    /**
     * @param array $argv
     * @param CLImate $climate
     */
    public function __construct(array $argv, CLImate $climate)
    {
        $this->argv = $argv;
        $this->climate = $climate;

        if ($this->isFlagSet('-v')) {
            $this->climate->comment('Running in verbose mode.');
            $this->isVerbose = true;
        }

        if (!isset($this->argv[1])) {
            $this->error('Please provide a <bold>base branch</bold> as the first argument.');
            return;
        }

        $this->baseBranch = 'origin/' . str_replace('origin/', '', $this->argv[1]);
        $this->currentBranch = trim(shell_exec('git rev-parse --verify HEAD'));

        if (empty($this->currentBranch)) {
            $this->error('Unable to get <bold>current</bold> branch.');
            return;
        }
    }

    /**
     * @param string $flag
     * @return bool
     */
    protected function isFlagSet($flag)
    {
        $isFlagSet = false;
        $argv = $this->argv;

        $key = array_search($flag, $argv, true);
        if (false !== $key) {
            unset($argv[$key]);
            $argv = array_values($argv);

            $isFlagSet = true;
        }

        $this->argv = $argv;
        return $isFlagSet;
    }

    /**
     * @param int $exitCode
     */
    protected function setExitCode($exitCode)
    {
        if (!is_int($exitCode)) {
            throw new \UnexpectedValueException('The exit code provided is not a valid integer.');
        }

        $this->exitCode = $exitCode;
    }

    /**
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }


    private function comment($comment)
    {
        if ($this->isVerbose) {
            $this->climate->comment($comment);
        }
    }

    /**
     */
    public function run()
    {
        try {
            $filter = new Filter([new SqlFileRule()], $this->getChangedFiles());
        } catch (FilterException $exception) {
            $this->error($exception->getMessage());
            return;
        }

        $fileDiff = $filter->filter()->getFilteredData();

        if (empty($fileDiff)) {
            $this->climate->info('No difference to compare.');
            return;
        }

        if ($this->isVerbose) {
            $fileDiffCount = count($fileDiff);
            $this->comment(
                'Checking ' . $fileDiffCount . ' ' .
                ngettext('file', 'files', $fileDiffCount) . ' for violations.'
            );
        }

        if (!$this->existsSqlint()) {
            $this->error('Unable to run sqlint executable.');
            return;
        }

        $sqlintOutput = $this->runSqlint($fileDiff);

        if (is_null($sqlintOutput)) {
            $this->climate->info('No violations to report.');
            return;
        }
        $this->error($sqlintOutput);
    }

    private function existsSqlint()
    {
        $return = shell_exec(sprintf("which %s", escapeshellarg(self::EXEC)));
        return !empty($return);
    }

    /**
     * Run phpcs on a list of files passed into the method
     *
     * @param array $files
     * @param string $ruleset
     * @return mixed
     */
    protected function runSqlint($files = [])
    {
        return shell_exec(self::EXEC . ' ' . implode(' ', $files) . ' 2>&1');
    }

    /**
     * Returns a list of files which are within the diff based on the current branch
     *
     * @return array
     */
    protected function getChangedFiles()
    {
        // Get a list of changed files (not including deleted files)
        $output = shell_exec(
            'git diff ' . $this->baseBranch . ' ' . $this->currentBranch . ' --name-only --diff-filter=d'
        );

        // Convert files into an array
        $output = explode(PHP_EOL, $output);

        // Remove any empty values
        return array_filter($output);
    }

    /**
     * @param string $message
     * @param int $exitCode
     */
    protected function error($message, $exitCode = 1)
    {
        $this->climate->error($message);
        $this->setExitCode($exitCode);
    }
}
