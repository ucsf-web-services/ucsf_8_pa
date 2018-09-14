<?php

namespace Drupal\ucsf_edu_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

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
 *       source: some_source_value
 *       para_type: paragraph_type
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
        $paragraph->save();
        //print_r($paragraph);
        print_r($this->configuration);
        if (is_string($value)) {
            return $value . 'hello world';
        }
        return null;
    }
}