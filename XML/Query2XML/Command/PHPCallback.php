<?php
/**This file contains the class XML_Query2XML_Command_PHPCallback.
*
* LICENSE:
* This source file is subject to version 2.1 of the LGPL
* that is bundled with this package in the file LICENSE.
*
* COPYRIGHT:
* Empowered Media
* http://www.empoweredmedia.com
* 481 Eighth Avenue Suite 1530
* New York, NY 10001
*
* @copyright Empowered Media 2006
* @license http://www.gnu.org/copyleft/lesser.html  LGPL Version 2.1
* @author Lukas Feiler <lukas.feiler@lukasfeiler.com>
* @package XML_Query2XML
* @version $Id$
* @access private
*/

/**XML_Query2XML_Command_PHPCallback extends the class XML_Query2XML_Command_Chain.
*/
require_once 'XML/Query2XML/Command/Chain.php';

/**Command class that invokes a callback function, using the return value as the
* data source.
*
* This command class does not accept a pre-processor.
*
* usage:
* <code>
* function myFunction($record) { ... }
* $commandObject = new XML_Query2XML_Command_PHPCallback('myFunction');
* </code>
*
* @access private
* @author Lukas Feiler <lukas.feiler@lukasfeiler.com>
* @version Release: @package_version@
* @copyright Empowered Media 2006
* @package XML_Query2XML
* @since Interface available since Release 1.5.0RC1
*/
class XML_Query2XML_Command_PHPCallback extends XML_Query2XML_Command_Chain implements XML_Query2XML_Command_DataSource
{
    private $_callback = null;
    private $_args = array();
    
    /**Constructor
    *
    * The following formats are supported for $callback:
    * - 'myFunction'
    * - 'myFunction(arg1, arg2, ...)'
    * - 'MyClass::myStaticMethod'
    * - 'MyClass::myStaticMethod(arg1, arg2, ...)'
    * You can also pass additional string arguments to the callback function by
    * specifing them within the opening and closing brace; e.g. 'Utils::limit(12)'
    * will result in Util::limit() being called by execute() with the $record as
    * the first and '12' as the second argument.
    * If you do not want to pass additional arguments to the callback function,
    * the opening and closing brace are optional.
    *
    * @throws XML_Query2XML_ConfigException If $callback is not callable.
    * @param mixed  $callback   
    * @param string $configPath The configuration path within the $options array.
    *                           This argument is optional.
    */
    public function __construct($callback, $configPath)
    {
        $this->configPath = $configPath;
        if ($this->configPath) {
            $this->configPath .= ': ';
        }
        
        $braceOpen = strpos($callback, '(');
        if ($braceOpen !== false) {
            $braceClose = strpos($callback, ')');
            if ($braceOpen + 1 < $braceClose) {
                $argsString = substr(
                    $callback, $braceOpen + 1, $braceClose - $braceOpen - 1
                );
                $this->_args = explode(',', str_replace(', ', ',', $argsString));
            }
            if ($braceOpen < $braceClose) {
                $callback = substr($callback, 0, $braceOpen);
            }
        }
        if (strpos($callback, '::') !== false) {
            $callback = split('::', $callback);
        }
        if (!is_callable($callback, false, $callableName)) {
            /*
            * unit tests: _applyColumnStringToRecord/
            *  throwConfigException_callback_function1.phpt
            *  throwConfigException_callback_function2.phpt
            *  throwConfigException_callback_method1.phpt
            *  throwConfigException_callback_method2.phpt
            */
            throw new XML_Query2XML_ConfigException(
                $this->configPath . 'The method/function "' . $callableName
                . '" is not callable.'
            );
        }
        $this->_callback = $callback;
    }
    
    /**Called by XML_Query2XML for every record in the result set.
    *
    * @param array $record An associative array.
    * @return mixed Whatever the callback function returned.
    */
    public function execute(array $record)
    {
        return call_user_func_array(
            $this->_callback,
            array_merge(array($record), $this->_args)
        );
    }
    
    /**This method is called by XML_Query2XML in case the asterisk shortcut was used.
    *
    * The interface XML_Query2XML_Command_DataSource requires an implementation of
    * this method.
    *
    * @param string $columnName The column name that is to replace every occurance
    *                           of the asterisk character '*' in any of the arguments
    *                           specified in the 'myFunction(arg1, arg2, ...)' or
    *                           'MyClass::myStaticMethod(arg1, arg2, ...)' notation.
    */
    public function replaceAsterisks($columnName)
    {
        foreach ($this->_args as $key => $arg) {
            $this->_args[$key] = str_replace('*', $columnName, $this->_args[$key]);
        }
    }
}
?>