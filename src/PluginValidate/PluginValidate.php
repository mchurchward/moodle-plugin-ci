<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\PluginValidate;

use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\CapabilityFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\ClassFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\MethodFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\FileTokens;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\FinderInterface;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\FunctionFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\VariableFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\LangFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\TableFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\TablePrefixFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\AbstractRequirements;

/**
 * Validates a plugin against a set of requirements.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PluginValidate
{
    /**
     * Results from validation.
     *
     * @var array
     */
    public $messages = [];

    /**
     * If the plugin is valid or not.
     *
     * @var bool
     */
    public $isValid = true;

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var AbstractRequirements
     */
    private $requirements;

    public function __construct(Plugin $plugin, AbstractRequirements $requirements)
    {
        $this->plugin       = $plugin;
        $this->requirements = $requirements;
    }

    /**
     * @param string $message
     */
    public function addError($message)
    {
        $this->messages[] = sprintf("\xE2\x9D\x8C %s", $message);
        $this->isValid    = false;
    }

    /**
     * @param string $message
     */
    public function addSuccess($message)
    {
        $this->messages[] = sprintf("<info>\xE2\x9C\x94</info> %s", $message);
    }

    /**
     * @param string $message
     */
    public function addWarning($message)
    {
        $this->messages[] = sprintf('<comment>!</comment> %s', $message);
    }

    /**
     * Add messages about finding or not finding tokens in a file.
     *
     * @param string     $type
     * @param FileTokens $fileTokens
     */
    public function addMessagesFromTokens($type, FileTokens $fileTokens)
    {
        foreach ($fileTokens->tokens as $token) {
            if ($token->hasTokenBeenFound()) {
                $message = method_exists($fileTokens, 'successmessage') ?
                    $fileTokens->successmessage(implode(' OR ', $token->tokens)) :
                    sprintf('In %s, found %s %s', $fileTokens->file, $type, implode(' OR ', $token->tokens));
                $this->addSuccess($message);
            } else {
                $message = method_exists($fileTokens, 'errormessage') ?
                    $fileTokens->errormessage(implode(' OR ', $token->tokens)) :
                    sprintf('In %s, failed to find %s %s', $fileTokens->file, $type, implode(' OR ', $token->tokens));
                $this->addError($message);
            }
        }
    }

    /**
     * Run verification of a plugin.
     */
    public function verifyRequirements()
    {
        $this->findRequiredFiles($this->requirements->getRequiredFiles());
        $this->findRequiredTokens(new FunctionFinder(), $this->requirements->getRequiredFunctions());
        $this->findRequiredVariables(new VariableFinder(), $this->requirements->getRequiredVariables());
        $this->findRequiredTokens(new ClassFinder(), $this->requirements->getRequiredClasses());
        $this->findRequiredTokens(new MethodFinder(), $this->requirements->getRequiredMethods());
        $this->findRequiredTokens(new LangFinder(), [$this->requirements->getRequiredStrings()]);
        $this->findRequiredTokens(new CapabilityFinder(), [$this->requirements->getRequiredCapabilities()]);
        $this->findRequiredTokens(new TableFinder(), [$this->requirements->getRequiredTables()]);
        $this->findRequiredTokens(new TablePrefixFinder(), [$this->requirements->getRequiredTablePrefix()]);
    }

    /**
     * Ensure a list of files exists.
     *
     * @param array $files
     */
    public function findRequiredFiles(array $files)
    {
        foreach ($files as $file) {
            if (file_exists($this->plugin->directory.'/'.$file)) {
                $this->addSuccess(sprintf('Found required file: %s', $file));
            } else {
                $this->addError(sprintf('Failed to find required file: %s', $file));
            }
        }
    }

    public function findRequiredVariables(FinderInterface $finder, array $requiredfilevariables) {
        foreach ($requiredfilevariables as $filename => $requiredvariables) {
            $filetokens = new FileTokens($filename);
            foreach ($requiredvariables as $variablename => $details) {
                $filetokens->mustHave($variablename);
            }

            try {
                $file = $this->plugin->directory.'/'.$filetokens->file;
                if (!file_exists($file)) {
                    $this->addWarning(sprintf('Skipping validation of missing or optional file: %s', $filetokens->file));
                } else {
                    $finder->variabledetails = $requiredvariables;
                    $messages = $finder->findTokens($file, $filetokens);
                    $this->addMessagesFromTokens($finder->getType(), $filetokens);
                    if (!empty($messages['error'])) {
                        $this->addError(sprintf('In %s, '.$messages['error'], $filetokens->file));
                    }
                    if (!empty($messages['success'])) {
                        $this->addSuccess(sprintf('In %s, '.$messages['success'], $filetokens->file));
                    }
                    if (!empty($messages['warning'])) {
                        $this->addWarning(sprintf('In %s, '.$messages['warning'], $filetokens->file));
                    }
                }
            } catch (\Exception $e) {
                $this->addError($e->getMessage());
            }
        }
    }

    /**
     * Find required tokens in a file.
     *
     * @param FinderInterface $finder
     * @param FileTokens[]    $tokenCollection
     */
    public function findRequiredTokens(FinderInterface $finder, array $tokenCollection)
    {
        foreach ($tokenCollection as $fileTokens) {
            if (!$fileTokens->hasTokens()) {
                continue;
            }
            $file = $this->plugin->directory.'/'.$fileTokens->file;

            if (!file_exists($file)) {
                $this->addWarning(sprintf('Skipping validation of missing or optional file: %s', $fileTokens->file));
                continue;
            }

            try {
                $finder->findTokens($file, $fileTokens);
                $this->addMessagesFromTokens($finder->getType(), $fileTokens);
            } catch (\Exception $e) {
                $this->addError($e->getMessage());
            }
        }
    }
}
