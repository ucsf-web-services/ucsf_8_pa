<?php

namespace Drupal\applenews\Controller;

use Drupal\applenews\ApplenewsPreviewBuilder;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ApplenewsPreviewController.
 *
 * @package Drupal\applenews\Controller
 */
class ApplenewsPreviewController extends ControllerBase {

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Serializer.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * Preview builder.
   *
   * @var \Drupal\applenews\ApplenewsPreviewBuilder
   */
  protected $previewBuilder;

  /**
   * ApplenewsPreviewController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger object.
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   Serializer object.
   * @param \Drupal\applenews\ApplenewsPreviewBuilder $preview_builder
   *   Preview builder object.
   */
  public function __construct(LoggerInterface $logger, Serializer $serializer, ApplenewsPreviewBuilder $preview_builder) {
    $this->logger = $logger;
    $this->serializer = $serializer;
    $this->previewBuilder = $preview_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.channel.applenews'),
      $container->get('serializer'),
      $container->get('applenews.preview_builder')
    );
  }

  /**
   * Generate preview ZIP file to download.
   *
   * @param string $entity_type
   *   String entity type.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity object.
   * @param int $revision_id
   *   revision ID.
   * @param string $template_id
   *   String template ID.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   Response object.
   */
  public function preview($entity_type, EntityInterface $entity, $revision_id, $template_id) {
    $context['template_id'] = $template_id;
    $filename = NULL;
    $entity_archive = TRUE;
    $entity_id = $entity->id();
    $entity_ids = [$entity_id];

    $data = $this->getDataArray($entity, $context);
    $this->exportToFile($entity_id, $entity_ids, $filename, $entity_archive, $data);
    $archive_path = $this->exportFilePath($entity_id);
    $archive = $archive_path . '.zip';

    $headers = ['Content-Type' => 'application/zip'];
    $filename = implode('-', ['applenews-preview', $entity_type, $entity_id]) . '.zip';
    $response = new BinaryFileResponse($archive, 200, $headers, FALSE);
    $response->setContentDisposition('attachment', $filename);

    return $response;
  }

  /**
   * Provides article data array.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity article attached to.
   * @param array $context
   *   An array of context.
   *
   * @return array
   *   An array of article data.
   */
  protected function getDataArray(EntityInterface $entity, array $context) {
    /** @var \ChapterThree\AppleNewsAPI\Document $document */
    $document = $this->serializer->normalize($entity, 'applenews', $context);

    return [
      'json' => Json::encode($document),
      'files' => [],
    ];
  }

  /**
   * Export article to file.
   *
   * @param int $entity_id
   *   Entity ID.
   * @param array $entity_ids
   *   An array of entity IDs.
   * @param string $filename
   *   String filename.
   * @param string $entity_archive
   *   String path.
   * @param array $data
   *   An array of article dta.
   *
   * @return int|null|string
   *   NULL if successful. URL for group of entities.
   */
  protected function exportToFile($entity_id, array $entity_ids, $filename, $entity_archive, array $data) {
    $preview = $this->previewBuilder->setEntity($entity_id, $filename, $entity_archive, $data);

    if (!empty($entity_id)) {
      $preview->toFile();
      try {
        $preview->archive($entity_ids);
      }
      catch (\Exception $e) {
        $this->logger->error('Could not create archive: @err', ['@err' => $e->getMessage()]);
      }
      return NULL;
    }
    else {
      $file_url = $preview->getArchiveFilePath();
      try {
        $preview->archive($entity_ids);
      }
      catch (\Exception $e) {
        $this->logger->error('Could not create archive: @err', ['@err' => $e->getMessage()]);
        return NULL;
      }
      return $file_url;
    }
  }

  /**
   * Provides archive URL.
   *
   * @param int $entity_id
   *   Entity ID.
   *
   * @return string
   *   String URL
   */
  protected function exportFilePath($entity_id) {
    return $this->previewBuilder->getEntityArchivePath($entity_id);
  }

}
