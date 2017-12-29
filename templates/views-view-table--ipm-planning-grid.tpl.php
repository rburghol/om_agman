<?php
/**
 * @file
 * Template to display a view as a week grouped table.
 *
 * - $title : The title of this group of rows.  May be empty.
 * - $header: An array of header labels keyed by field id.
 * - $header_classes: An array of header classes keyed by field id.
 * - $fields: An array of CSS IDs to use for each field id.
 * - $classes: A class or classes to apply to the table, based on settings.
 * - $row_classes: An array of classes to apply to each row, indexed by row
 *   number. This matches the index in $rows.
 * - $rows: An array of row items. Each row is an array of content.
 *   $rows are keyed by row number, fields within rows are keyed by field ID.
 * - $field_classes: An array of classes to apply to each field, indexed by
 *   field id, then row number. This matches the index in $rows.
 * @ingroup views_templates
 */
$yrwk_rows = array();
$yrwk_master = array(); //@todo: had yrwk_rows as foreach and an attribute -- 
                        // corrected but if this breaks, 
                        // go ahead and replace all "yrwk_master" with "yrwk_rows" even 
                        // though that is dodgey code
$yrwk_dels = array(); // keep track of delimiters
// iterate through rows and group by the selected date field
// this is hard coded for this display to be the week field
foreach ($rows as $row_count => $row) {
 if (isset($row['yrwk'])) {
   $yrwk = $row['yrwk'];
   foreach ($row as $key => $values) {
     $yrwk_master[$yrwk][$key][] = $values;
     //$yrwk_rows[$yrwk][$key][] = $values;
   }
 } else {
   //dpm($row, "Row does not have 'yrwk' column");
 }
}
$i = 0;
$efficacy = array(
  0=> 'N/A',
  1=> 'Excellent',
  2=> 'Good',
  3=> 'Good/Fair',
  4=> 'Fair',
  5=> 'Poor',
  6=> 'None',
);
$rendered_rows = array();

#foreach ($yrwk_rows as $yrwk => $yrwk_rows) {
foreach ($yrwk_master as $yrwk => $yrwk_rows) {
 // apply default grouping by unique
 foreach ($yrwk_rows as $key => $values) {
   $row[$key] = implode(', ', array_unique($values));
 }
 // find best efficacy
 $eff_cols = array('propvalue', 'propvalue_1', 'propvalue_2', 'propvalue_3', 'propvalue_4');
 foreach ($eff_cols as $col) {
   $eff = array();
   if (isset($yrwk_rows[$col])) {
     foreach ($yrwk_rows[$col] as $idx) {
       if (!empty($idx)) {
         $eff[] = $idx;
       }
     }
     $eff_index = empty($eff) ? 0 : min($eff);
     $row[$col] = $efficacy[$eff_index];
   }
 }
 $rendered_rows[] = $row;
}
//dpm($rendered_rows, 'rendered rows');
$rows = $rendered_rows;

?>
<table <?php if ($classes) { print 'class="'. $classes . '" '; } ?><?php print $attributes; ?>>
  <?php if (!empty($title)) : ?>
    <caption><?php print $title; ?></caption>
  <?php endif; ?>
  <?php if (!empty($header)) : ?>
    <thead>
      <tr>
        <?php foreach ($header as $field => $label): ?>
          <th <?php if ($header_classes[$field]) { print 'class="'. $header_classes[$field] . '" '; } ?>>
            <?php print $label; ?>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
  <?php endif; ?>
  <tbody>
    <?php foreach ($rows as $row_count => $row): ?>
      <tr <?php if ($row_classes[$row_count]) { print 'class="' . implode(' ', $row_classes[$row_count]) .'"';  } ?>>
        <?php foreach ($row as $field => $content): 
        // 
        ?>
          <td <?php if ($field_classes[$field][$row_count]) { print 'class="'. $field_classes[$field][$row_count] . '" '; } ?><?php print drupal_attributes($field_attributes[$field][$row_count]); ?>>
            <?php print $content; ?>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
