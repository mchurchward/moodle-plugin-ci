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

/**
 * A list of tokens to find in a file.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MethodTokens extends FileTokens
{
    /**
     * @var string $class The name of the class to search in.
     */
    public $classname;

    /**
     * @param string $file
     * @param string $classname
     */
    public function __construct($file, $classname)
    {
        $this->classname = $classname;
        parent::__construct($file);
    }

    /**
     * Factory method for quality of life.
     *
     * @param string $file
     * @param string $classname
     *
     * @return MethodTokens
     */
    public static function create($file, $classname)
    {
        return new self($file, $classname);
    }

    public function successmessage($token) {
        return sprintf('In %s, found method %s::%s', $this->file, $this->classname, $token);
    }

    public function errormessage($token) {
        return sprintf('In %s, failed to find method %s::%s', $this->file, $this->classname, $token);
    }
}
