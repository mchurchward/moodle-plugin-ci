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

namespace Moodlerooms\MoodlePluginCI\PluginValidate\Finder;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Scalar\LNumber;

/**
 * Finds Moodle capabilities in a db/access.php file.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class VariableFinder extends AbstractParserFinder
{
    public function getType()
    {
        return 'variable';
    }

    public function findTokens($file, FileTokens $fileTokens)
    {
        $messages = ['success' => '', 'error' => '', 'warning' => ''];
        $statements = $this->parser->parseFile($file);

        foreach ($this->filter->filterAssignments($statements) as $assign) {
            $errmessage = '';
            $sucmessage = '';
            $check = false;
            if ($assign->var instanceof PropertyFetch) {
                $variable = $assign->var->var->name.'->'.$assign->var->name;
                $check = true;
            } else if ($assign->var instanceof Variable) {
                $variable = $assign->var->name;
                $check = true;
            }
            if ($check) {
                $fileTokens->compare($variable);
                if ($this->variabledetails[$variable]->type !== null) {
                    $types = explode('||', $this->variabledetails[$variable]->type);
                    $found = false;
                    foreach ($types as $type) {
                        if (get_class($assign->expr) == $type) {
                            $found = true;
                        }
                    }
                    if (!$found) {
                        $errmessage = 'the $'.$variable.' variable is not the correct type';
                    }
                }
                if ($this->variabledetails[$variable]->value !== null) {
                    if ($this->variabledetails[$variable]->type == 'PhpParser\Node\Expr\ConstFetch') {
                        if ($this->variabledetails[$variable]->value != $assign->expr->name->parts[0]) {
                            $errmessage .= (empty($errmessage) ? 'the $'.$variable.' variable ' : ' and ') . 'is not the value ' .
                                $this->variabledetails[$variable]->value;
                        }
                    } else if ($this->variabledetails[$variable]->value != $assign->expr->value) {
                        $errmessage .= (empty($errmessage) ? 'the $'.$variable.' variable ' : ' and ') . 'is not the value ' .
                            $this->variabledetails[$variable]->value;
                    }
                }
                if (empty($errmessage)) {
                    $sucmessage = 'the $'.$variable.' has the correct type and value';
                }
            }
            if (!empty($errmessage)) {
                $messages['error'] .= empty($messages['error']) ? $errmessage : ",\nand " . $errmessage;
            } else if (!empty($sucmessage)) {
                $messages['success'] .= empty($messages['success']) ? $sucmessage : ",\nand " . $sucmessage;
            }
        }
        return $messages;
    }
}
