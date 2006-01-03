<?php
/**
 * patForms Rule German Zip Code
 *
 * $Id$
 *
 * @package		patForms
 * @subpackage	Rules
 */

/**
 * patForms Rule German Zip Code
 *
 * This only checks for the format of a german zip code.
 *
 * @package		patForms
 * @subpackage	Rules
 * @author		Stephan Schmidt <schst@php-tools.net>
 * @license		LGPL, see license.txt for details
 * @link		http://www.php-tools.net
 */
class patForms_Rule_GermanZipCode extends patForms_Rule
{
   /**
	* name of the rule
	*
	* @abstract
	* @access	private
	*/
	var	$ruleName = 'GermanZipCode';

   /**
	* define error codes and messages for the rule
	*
	* @access	private
	* @var		array	$validatorErrorCodes
    * @todo     translate error messages
	*/
	var	$validatorErrorCodes  =   array(
		"C"	=>	array(
			1	=>	"The value is not a german zip code.",
		),
		"de" =>	array(
			1	=>	"Der Wert ist keine deutsche Postleitzahl.",
		),
		"fr" =>	array(
			1	=>	"The value is not a german zip code.",
		)
	);

   /**
	* method called by patForms or any patForms_Element to validate the
	* element or the form.
	*
	* @access	public
	* @param	object patForms	form object
	*/
	function applyRule( &$element, $type = PATFORMS_RULE_BEFORE_VALIDATION )
	{
		if (preg_match('/[0-9]{5}/', $element->getValue())) {
			return true;
		}
		$this->addValidationError( 1 );
		return false;	
	}
}
?>