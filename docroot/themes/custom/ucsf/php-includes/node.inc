<?php

/**
 * @file
 * Theme and preprocess functions for node template.
 */

// use Drupal\paragraphs\Entity\Paragraph;

/**
 * Implements hook_preprocess_node() for node.html.twig.
 */
// function ucsf_preprocess_node(&$variables) {
//   $node = $variables['node'];
//   if ($node->getType() == 'article') {
//     if (!empty($node->field_content_panel->getValue())) {
//       foreach ($node->field_content_panel->getValue() as $value) {
//         if (!empty($value['target_id'])) {
//           $paragraph = Paragraph::load($value['target_id']);
//           if ($paragraph->bundle() == "parallax_img_txt_overlay_wrapper") {
//             if (!empty($paragraph->field_move_below_banner->getValue())) {
//               $variables['move_below_banner'] = TRUE;

//             }
//           }
//         }
//       }
//     }
//   }
// }
