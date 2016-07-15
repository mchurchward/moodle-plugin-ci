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

namespace Moodlerooms\MoodlePluginCI\PluginValidate\Requirements;

use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\FileTokens;

/**
 * Theme plugin requirements.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ThemeRequirements extends GenericRequirements
{
    public function getRequiredFiles()
    {
        return array_merge(parent::getRequiredFiles(), [
            'config.php',
        ]);
    }

    public function getRequiredVariables()
    {
        return array_merge(parent::getRequiredVariables(), [
            'config.php' => [
                'THEME->name' => (object)['value' => null, 'type' => 'PhpParser\Node\Scalar\String_'],
                'THEME->parents' => (object)['value' => null, 'type' => 'PhpParser\Node\Expr\Array_'],
            ],
        ]);
    }

    public function getRequiredStrings()
    {
        return FileTokens::create($this->getLangFile())->mustHave('pluginname')->mustHave('choosereadme');
    }
}