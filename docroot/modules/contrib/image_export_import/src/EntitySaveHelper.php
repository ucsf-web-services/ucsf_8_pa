<?php

namespace Drupal\image_export_import;

use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Html;
use Drupal\node\Entity\NodeType;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityFieldManager;

/**
 * Service for Deleting the entity.
 */
class EntitySaveHelper {

  /**
   * Drupal File System.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $entityTypeManager;

  /**
   * Drupal File System.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Drupal\Core\Entity\EntityFieldManager definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $fieldManager;

  /**
   * Constructor for \Drupal\image_export_import\EntitySaveHelper class.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity type manager.
   * @param \Drupal\Core\File\FileSystem $file_system
   *   The Form Builder.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entityQuery
   *   The Query Builder.
   * @param \Drupal\Core\Entity\EntityFieldManager $fieldManager
   *   The Field manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileSystem $file_system, QueryFactory $entityQuery, EntityFieldManager $fieldManager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->entityQuery = $entityQuery;
    $this->fieldManager = $fieldManager;
  }

  /**
   * Get all content types from CMS.
   */
  public function getAllContentTypes() {
    $contentTypes = NodeType::loadMultiple();

    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    return $contentTypesList;
  }

  /**
   * Get file handle from uri.
   *
   * @param string $uri
   *   URI of file.
   *
   * @return resource
   *   Resource handler.
   */
  public function getFileHandler($uri) {
    return fopen($this->fileSystem->realpath($uri), "r");
  }

  /**
   * Batch finish callback.
   */
  public static function importImageFromCsvFinishedCallback($success, $results, $operations) {
    $messenger = \Drupal::messenger();
    if ($success) {
      // Here we could do something meaningful with the results.
      // We just display the number of nodes we processed.
      $messenger->addMessage(t('@count results processed.', ['@count' => $results]));
    }
    else {
      $messenger->addMessage(t('Finished with an error.'));
    }
  }

  /**
   * Return csv data.
   *
   * @param resource $handle
   *   Resource handler.
   *
   * @return array
   *   Array of csv row.
   */
  public function getCsvData($handle) {
    return fgetcsv($handle, 0, ',', '"');
  }

  /**
   * Returns image fields based on content type.
   *
   * @param mixed $bundle
   *   Content type name.
   * @param mixed $entity_type_id
   *   This will contains nid.
   */
  public function getAllImageFields($bundle, $entity_type_id) {
    $bundleFields = [];
    foreach ($this->fieldManager->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
      // Get only image type fields.
      if (!empty($field_definition->getTargetBundle()) && $field_definition->getType() == 'image') {
        // $bundleFields[$field_name]['type'] = $field_definition->getType();
        $bundleFields[$field_name] = $field_definition->getLabel();
      }
    }
    return $bundleFields;
  }

  /**
   * Write data in csv file to export functionality.
   *
   * @param mixed $filename
   *   Name of csv file.
   * @param mixed $content_types
   *   Content type name.
   * @param mixed $image_fields
   *   Image field name.
   * @param mixed $export_body
   *   Check user want to export body summary or not.
   *
   * @return mixed
   *   Return csv file url.
   */
  public function createCsvFileExportData($filename, $content_types, $image_fields, $export_body = NULL) {
    // Get file path in CMS.
    $path = $this->fileSystem->realpath("public://$filename");
    $file = fopen($path, 'w');
    // Send the column headers.
    if (!empty($export_body)) {
      fputcsv($file, [
        'Nid',
        "Node_" . $content_types . '_Title',
        $image_fields, 'IMG_Alt',
        'IMG_title',
        'Node_Summary',
        'Node_Description',
      ]);
    }
    else {
      fputcsv($file, [
        'Nid',
        "Node_" . $content_types . '_Title',
        $image_fields,
        'IMG_Alt',
        'IMG_title',
      ]);
    }
    // Create migrate_images if not exists.
    if (!is_dir($this->fileSystem->realpath("public://migrate_images"))) {
      mkdir($this->fileSystem->realpath("public://migrate_images"), 0777, TRUE);
      chmod($this->fileSystem->realpath("public://migrate_images"), 0777);
    }
    // Sample data. This can be fetched from mysql too.
    $nids = \Drupal::entityQuery('node')->condition('type', $content_types)->execute();
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    foreach ($nodes as $node) {
      $row = $img_title = $alt = $basename = [];
      $row[0] = $node->get('nid')->value;
      $row[1] = $node->get('title')->value;
      // Check image exits or not.
      if (!empty($node->get($image_fields)->target_id)) {
        $total_images = count($node->get($image_fields)->getValue());
        // Check multiple image value for this node.
        if ($total_images >= 1) {
          $image_index = 0;
          foreach ($node->get($image_fields)->getValue() as $image_val) {
            $image_data = [];
            $image_data = $this->entityTypeManager->getStorage('file')->load($image_val['target_id']);
            if (!empty($image_data)) {
              $basename[$image_index] = basename($image_data->getFileUri());
              // Copy files under migrate_images directory.
              $src = $this->fileSystem->realpath($image_data->getFileUri());
              $dest = $this->fileSystem->realpath("public://migrate_images");
              shell_exec("cp -r $src $dest");
            }
            $alt[$image_index] = ($image_val['alt']) ? $image_val['alt'] : '';
            $img_title[$image_index] = ($image_val['title']) ? $image_val['title'] : '';
            $image_index++;
          }
          // Concate image values for multiple fields.
          $row[2] = implode("|", $basename);
          $row[3] = implode("|", $alt);
          $row[4] = implode("|", $img_title);
        }
      }
      if (!empty($export_body)) {
        $row[5] = ($node->get('body')->summary) ? $node->get('body')->summary : '';
        $row[6] = ($node->get('body')->value) ? $node->get('body')->value : '';
      }
      // Write each record in csv file.
      fputcsv($file, $row);
    }
    fclose($file);
    return $path;
  }

  /**
   * Get orphan files from CMS.
   */
  public function deleteOrphanFiles() {
    // Create database connection.
    $query = \Drupal::database()
      ->select('file_managed', 'fm')
      ->fields('fm', ['fid'])
      ->fields('fu', ['fid']);
    $query->addJoin('left', 'file_usage', 'fu', 'fm.fid=fu.fid');
    $query->condition('fu.fid', NULL, 'IS');
    $data = $query->execute();
    // Get all images from database.
    $results = $data->fetchAll(\PDO::FETCH_OBJ);
    foreach ($results as $row) {
      // Remove file from system.
      file_delete($row->fid);
    }
  }

  /**
   * This function runs the batch processing and creates terms.
   */
  public static function importImageFromCsv($row, &$context) {
    // Public static function importImageFromCsv($row, &$context) {.
    $operation_details = '';
    $body_value = !empty($row['data'][6]) ? html_entity_decode($row['data'][6]) : '';
    $body_summary = !empty($row['data'][5]) ? html_entity_decode($row['data'][5]) : '';
    // Check title field not empty.
    if (!empty($row['data'][0])) {
      // Check exiting node in CMS.
      $nid = self::checkExitingNode($row['data'][0], $row['content_type'], 'nid');
      $node = Node::load($nid);
      // Update node title and body after user confirmation.
      if (!empty($node)) {
        $node->set('title', Html::escape($row['data'][1]));
        if (!empty($row['save_body']) && !empty($body_value)) {
          $node->set('body', [
            'value' => $body_value,
            'summary' => $body_summary,
            'format' => 'full_html',
          ]
          );
        }
        // Check image name in CMS.
        if (!empty($row['data'][2])) {
          $media_image = @self::uploadOffersMedia($row['data'][2], $row['data'][3], $row['data'][4]);
          // Check image exists or not.
          if (!empty($media_image)) {
            // Update image field in content type.
            $image_field_name = $row['image_field'];
            $node->{$image_field_name}->setValue($media_image);
          }
        }
        // Save node object with updated value.
        $operation_details = ' updated successfully.';
        $node->save();
      }
    }
    elseif ((empty($row['data'][0]) && !empty($row['data'][1])) && !empty($row['new_node'])) {
      // Update image field for offer data.
      $node_object = @self::checkExitingNode($row['data'][1], $row['content_type'], 'title');
      if (!empty($row['data'][2]) && empty($node_object)) {
        $media_image = @self::uploadOffersMedia($row['data'][2], $row['data'][3], $row['data'][4]);
        if (!empty($media_image)) {
          $image_field_name = $row['image_field'];
          $content_type = $row['content_type'];
          // Create new node.
          $node = Node::create(
              [
                'type' => $content_type,
                'title' => Html::escape($row['data'][1]),
                'body' => [
                  'value' => $body_value,
                  'summary' => $body_summary,
                  'format' => 'full_html',
                ],
                $image_field_name => $media_image,
              ]
          );
          $operation_details = ' imported successfully.';
          $node->save();
        }
      }
    }

    $context['message'] = t('Running Batch "@id" @details', ['@id' => $row['data'][1], '@details' => $operation_details]
    );
    $context['results'] = $row['result'];
  }

  /**
   * Check exiting node in CMS.
   *
   * @param mixed $item_code
   *   Nid is here.
   * @param mixed $node_type
   *   Node type name.
   * @param mixed $field_name
   *   Image field name.
   *
   * @return mixed
   *   Return nid
   */
  public static function checkExitingNode($item_code, $node_type, $field_name) {
    $nodes = \Drupal::entityQuery('node')->condition('type', $node_type)->condition($field_name, $item_code, 'IN')->execute();
    $nid = key($nodes);
    if (!empty($nodes)) {
      return $nodes[$nid];
    }
  }

  /**
   * Create and update Media Image content.
   *
   * @param mixed $file_name
   *   Name of image file.
   * @param mixed $alt
   *   Alt tag of image.
   * @param mixed $title
   *   Title tag of image.
   *
   * @return mixed
   *   Return Media object.
   */
  public function uploadOffersMedia($file_name, $alt, $title) {
    // Get local image directory path. field_offer_media.
    $multi_image = explode('|', $file_name);
    $multi_alt = explode('|', $alt);
    $multi_title = explode('|', $title);
    if (count($multi_image) >= 1) {
      $i = 0;
      foreach ($multi_image as $value) {
        $image_local_dir = \Drupal::service('file_system')->realpath("public://migrate_images");
        // Save Image in local from remote data.
        $data = file_get_contents($image_local_dir . "/" . $value);
        if (!empty($data)) {
          $file = file_save_data($data, "public://" . $value, FILE_EXISTS_RENAME);
          // Check exiting media entity for this node.
          $media_image[$i] = [
            'target_id' => $file->id(),
            'alt' => !empty($multi_alt[$i]) ? Html::escape($multi_alt[$i]) : "",
            'title' => !empty($multi_title[$i]) ? Html::escape($multi_title[$i]) : "",
          ];
        }
        $i++;
      }
      return $media_image;
    }
  }

  /**
   * Get an appropriate archiver class for the file.
   *
   * @param string $file
   *   The file path.
   */
  public function getArchiver($file) {
    $extension = strstr(pathinfo($file)['basename'], '.');
    switch ($extension) {
      case '.tar.gz':
      case '.tar':
        $this->archiver = new \PharData($file);
        break;

      case '.zip':
        $this->archiver = new \ZipArchive($file);
        $this->archiver->open($file);
      default:
        break;
    }
    return $this->archiver;
  }

}
