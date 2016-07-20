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
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\MethodTokens;

/**
 * Question plugin requirements.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class QuestionRequirements extends GenericRequirements
{
    public function getRequiredFiles()
    {
        return array_merge(parent::getRequiredFiles(), [
            'questiontype.php',
            'question.php',
            'edit_'.$this->plugin->name.'_form.php',
        ]);
    }

    public function getRequiredTablePrefix()
    {
        return FileTokens::create('db/install.xml')->mustHaveAny(['qtype_', 'question_']);
    }

    public function getRequiredStrings()
    {
        return FileTokens::create($this->getLangFile())
            ->mustHave('pluginname')
            ->mustHave('pluginnamesummary')
            ->mustHave('pluginnameediting')
            ->mustHave('pluginnameadding')
            ->mustHave('pluginname_help');
     }

    public function getRequiredMethods()
    {
        return [
            MethodTokens::create('edit_'.$this->plugin->name.'_form.php', 'qtype_'.$this->plugin->name.'_edit_form')->mustHave('qtype'),
            MethodTokens::create('question.php', 'qtype_'.$this->plugin->name.'_question')->mustHave('get_expected_data')->mustHave('get_correct_response'),
        ];
    }

    public function getRequiredClasses()
    {
        return [
            FileTokens::create('questiontype.php')->mustHave('qtype_'.$this->plugin->name),
            FileTokens::create('question.php')->mustHave('qtype_'.$this->plugin->name.'_question'),
            FileTokens::create('edit_'.$this->plugin->name.'_form.php')->mustHave('qtype_'.$this->plugin->name.'_edit_form'),
            FileTokens::create('/backup/moodle2/backup_qtype_'.$this->plugin->name.'_plugin.class.php')
                ->mustHave('backup_qtype_'.$this->plugin->name.'_plugin'),
            FileTokens::create('/backup/moodle2/restore_qtype_'.$this->plugin->name.'_plugin.class.php')
                ->mustHave('restore_qtype_'.$this->plugin->name.'_plugin'),
        ];
    }
}