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
 *       file: path to csv file
 *
 * @endcode
 *
 * This adds a string to the end of a value.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "data_lookup"
 * )
 */

class data_lookup extends ProcessPluginBase {
    
    public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property){
        // If the $value field which is the source value is a string add hello world to the end of it.
        //print_r($value."\n");
        $lines= file($this->configuration['file']);
        $pattern = '/(?P<name>'.$value.') *,(?P<digit>\d+)/i';
        foreach($lines as $key){
            //print_r($key."\n");
            if(preg_match($pattern,$key,$matches)){
                //print_r($matches[name]." ".$matches[digit]."\n");
                $value = $matches[digit];
            }
        }
        //$row->setSourceProperty($this->configuration['source'],$value);
        return $value;
    }
}