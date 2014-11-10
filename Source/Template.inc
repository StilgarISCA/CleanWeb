<?php
/***************************************************************************
 * Object: Template.inc
 * Author: Glenn Hoeppner
 * Date Started: 2/1/05
 * Date Finished: 2/2/05
 *
 * PHP 5 class to process template files.  The object allows variable names
 * and values to be stored in an associative array.  These names/values can
 * then be applied to an HTML template file with some special markup, and the
 * values will be inserted into the corresponding "special" markup in the
 * template file.
 *
 * Note: This object is based on code published in the October, 2004 issue
 * of PHP|architect.
 ***************************************************************************/
class Template
{
   // private properties
   private $_templateValues = array();  // stores our values

   /*************************************************************************
    * Method: AddValue(str, mixed)
    * Accepts: name of template variable, value of template variable
    * Returns: nothing
    *
    * Stores template names and values in the internal associative array.
    *************************************************************************/
   public function AddValue( $variable, $value )
   {
      $this->_templateValues[ $variable ] = $value;
   } // end method AddValue()

   /*************************************************************************
    * Method: AddValueByRef(str, mixed)
    * Accepts: name of template variable, value of template variable
    * Returns: nothing
    *
    * Stores template names and values in the internal associative array
    * BY REFERENCE, this is useful for storing things like objects and arrays.
    *************************************************************************/
   public function AddValueByRef( $variable, &$value )
   {
      $this->_templateValues[ $variable ] = &$value;
   } // end method AddValueByRef()

   /*************************************************************************
    * Method: Process(str)
    * Accepts: filename of template to process
    * Returns: output of processed template
    *
    * Uses the $ operator to load all the stored values into variables and
    * inserts them into the template file (which is then returned).
    *************************************************************************/
   public function Process( $template )
   {
      foreach ( $this->_templateValues as $variable => $value )
         $$variable = $value;
      ob_start();
      require( $template );
      $content = ob_get_contents();
      ob_end_clean();

      return $content;
   } // end method Process()

} // end class cTemplateProcessing
?>