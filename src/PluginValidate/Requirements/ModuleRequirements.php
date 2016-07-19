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
 * Module requirements.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ModuleRequirements extends GenericRequirements
{
    protected function getLangFile()
    {
        return 'lang/en/'.$this->plugin->name.'.php';
    }

    public function getRequiredFiles()
    {
        return array_merge(parent::getRequiredFiles(), [
            'lib.php',
            'view.php',
            'index.php',
            'mod_form.php',
            'db/install.xml',
            'db/access.php',
            'backup/moodle2/backup_'.$this->plugin->name.'_activity_task.class.php',
            'backup/moodle2/backup_'.$this->plugin->name.'_settingslib.php',
            'backup/moodle2/backup_'.$this->plugin->name.'_stepslib.php',
            'backup/moodle2/restore_'.$this->plugin->name.'_activity_task.class.php',
            'backup/moodle2/restore_'.$this->plugin->name.'_stepslib.php',
        ]);
    }

    public function getRequiredFunctions()
    {
        return [
            FileTokens::create('lib.php')
                ->mustHave($this->plugin->name.'_add_instance')
                ->mustHave($this->plugin->name.'_update_instance')
                ->mustHave($this->plugin->name.'_delete_instance'),
            FileTokens::create('db/upgrade.php')
                ->mustHave('xmldb_'.$this->plugin->name.'_upgrade'),
        ];
    }

    public function getRequiredVariables()
    {
        return array_merge(parent::getRequiredVariables(), [
            'db/log.php' => [
                'logs' => (object)['value' => null, 'type' => null],
            ],
        ]);
    }

    public function getRequiredStrings()
    {
        return FileTokens::create($this->getLangFile())
            ->mustHaveAny(['modulename', 'pluginname'])
            ->mustHave($this->plugin->name.':addinstance')
            ->mustHave('modulenameplural')
            ->mustHave('pluginadministration');
     }

    public function getRequiredClasses()
    {
        return [
            FileTokens::create('mod_form.php')->mustHave('mod_'.$this->plugin->name.'_mod_form'),
        ];
    }

    public function getRequiredCapabilities()
    {
        return FileTokens::create('db/access.php')->mustHave('mod/'.$this->plugin->name.':addinstance');
    }

    public function getRequiredTables()
    {
        return FileTokens::create('db/install.xml')->mustHave($this->plugin->name);
    }

    public function getRequiredTablePrefix()
    {
        return FileTokens::create('db/install.xml')->mustHaveAny([$this->plugin->name, $this->plugin->component]);
    }
}
