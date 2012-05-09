<?php if (!defined('APPLICATION')) exit();

class PostEdits extends Gdn_Form{

    /**
     * This is a modified function from an existing method in Gdn_Form
     * This is modified to disable any categores with children
     *
     */
   public function CategoryDropDown($FieldName = 'CategoryID', $Options = FALSE) {
      $Value = ArrayValueI('Value', $Options); // The selected category id
      $CategoryData = GetValue('CategoryData', $Options, CategoryModel::Categories());
      // Sanity check
      if (is_object($CategoryData))
         $CategoryData = (array)$CategoryData;
      else if (!is_array($CategoryData))
         $CategoryData = array();

      //print_r($CategoryData); die;
      // Respect category permissions (remove categories that the user shouldn't see).
      $SafeCategoryData = array();
      foreach ($CategoryData as $CategoryID => $Category) {
         if ($Value != $CategoryID) {
            if ($Category['CategoryID'] <= 0)
               continue;
            if ($Category['Archived'])
               continue;
         }

         $SafeCategoryData[$CategoryID] = $Category;
      }

      $this->InputPrefix = 'Discussion'; //MUST add this!! to set the "name" attribute correctly

      // Opening select tag
      $Return = '<select';
      $Return .= $this->_IDAttribute($FieldName, $Options);
      $Return .= $this->_NameAttribute($FieldName, $Options);
      $Return .= $this->_AttributesToString($Options);
      $Return .= ">\n";

      // Get value from attributes
      if ($Value === FALSE)
         $Value = $this->GetValue($FieldName);
      if (!is_array($Value))
         $Value = array($Value);

      // Prevent default $Value from matching key of zero
      $HasValue = ($Value !== array(FALSE) && $Value !== array('')) ? TRUE : FALSE;

      // Start with null option?
      $IncludeNull = GetValue('IncludeNull', $Options);
      if ($IncludeNull === TRUE)
         $Return .= '<option value=""></option>';

      // Show root categories as headings (ie. you can't post in them)?
      $DoHeadings = C('Vanilla.Categories.DoHeadings');

      // If making headings disabled and there was no default value for
      // selection, make sure to select the first non-disabled value, or the
      // browser will auto-select the first disabled option.
      $ForceCleanSelection = ($DoHeadings && !$HasValue);

      // Write out the category options
      if (is_array($SafeCategoryData)) {
         foreach($SafeCategoryData as $CategoryID => $Category) {
            $Depth = GetValue('Depth', $Category, 0);
            $Disabled = ($Depth == 1 || $Category['PermsDiscussionsAdd'] == 0);
            $Selected = in_array($CategoryID, $Value) && $HasValue;
            if ($ForceCleanSelection && $Depth > 1) {
               $Selected = TRUE;
               $ForceCleanSelection = FALSE;
            }

            $Return .= '<option value="' . $CategoryID . '"';
            if ($Disabled)
               $Return .= ' disabled="disabled"';
            else if ($Selected)
               $Return .= ' selected="selected"'; // only allow selection if NOT disabled

            $Name = GetValue('Name', $Category, 'Blank Category Name');
            if ($Depth > 1) {
               $Name = str_pad($Name, strlen($Name)+$Depth-1, ' ', STR_PAD_LEFT);
               $Name = str_replace(' ', '&#160;', $Name);
            }

            $Return .= '>' . $Name . "</option>\n";
         }
      }
      return $Return . '</select>';
   }

}

