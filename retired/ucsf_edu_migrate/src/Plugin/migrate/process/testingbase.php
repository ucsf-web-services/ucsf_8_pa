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
 *       plugin: your_plugin_name
 *       source: some_source_value(useless)
 *       para_type: paragraph_type
 *       fields:
 *         -
 *           field_a: source
 *           field_b: source
 *       csv:
 *       
 * @endcode
 *
 * This adds a string to the end of a value.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "testingbase"
 * )
 */

class testingbase extends ProcessPluginBase {
    
    public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property){
        // If the $value field which is the source value is a string add hello world to the end of it.
        $paragraph = Paragraph::create(['type' => $this->configuration['para_type']]);
        $fields = $paragraph->getFieldDefinitions();
        //$paragraph->set($this->configuration['destination'], $this->configuration['source']); 
        //$fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'article');  
        foreach($paragraph->getFields() as $test){
        }
        $dest = $this->configuration['fields'][0];
        //print_r($dest);
        $value;
        if(!empty($dest)){
            foreach($dest as $source => $field) {
                //print_r($paragraph->getFieldDefinition()."\n");
                //print_r($field->getType()."\n");
            
                $value = $row->getSourceProperty($field);
                if ($field[0] == '@') {
                    $field = substr($field, 1);
                    $value = $row->getDestinationProperty($field);
                }
                //print_r('"'.$value.'"'."\n");
                $paragraph->set($source,$value);
            
            }
        }
        //print_r($this->configuration);
        //print_r($value."\n");
        $paragraph->save();
        if(!empty($this->configuration['csv'])){
            $line = "gallery," . 0 . "," .$paragraph->id(). "," .$paragraph->id(). "," . "en," . 0 . "," .$value.",".$value."\n";
            file_put_contents("public://paragraph_field_gallery_items.csv",$line,FILE_APPEND);
            //print_r($paragraph->id()."\n");
        }
        return $paragraph;
    }
}