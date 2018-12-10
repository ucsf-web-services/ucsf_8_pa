<?php

namespace Drupal\ucsf_edu_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity;
    
/**
 * Returns an href url string from the source string if link markup exists.
 *
 * Example:
 *
 * @code
 * process:
 *   field_your_field_name:
 *     -
 *       plugin: data_lookup
 *       source: some_source_value
 *
 * @endcode
 *
 * This adds a string to the end of a value.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "data_condense"
 * )
 */

class data_condense extends ProcessPluginBase {
    
    public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property){
        // If the $value field which is the source value is a string add hello world to the end of it.
        //file_put_contents("name.csv",'');
        $line = '"'.$value.'",';
        $value = preg_replace("/[^A-Za-z0-9]| /",'', $value);
        $line .= $value."\n";
        //file_put_contents("cus_author_name.csv",$line,FILE_APPEND);
        return $value;
    }
}