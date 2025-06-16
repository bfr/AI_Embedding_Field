<?php

namespace Drupal\ai_embedding_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'embedding_preview' formatter.
 *
 * @FieldFormatter(
 *   id = "embedding_preview",
 *   label = @Translation("Embedding debug preview"),
 *   field_types = {
 *     "ai_embedding_field"
 *   }
 * )
 */
class EmbeddingPreviewFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\node\NodeInterface $node */
    $node = $items->getEntity();
    $field = $items->getFieldDefinition();
    $settings = $field->getSettings();

    $text_parts = [];
    foreach ($settings['source_fields'] ?? [] as $source_field) {
      if ($node->hasField($source_field)) {
        $value = $node->get($source_field)->value ?? '';
        $text_parts[] = "<strong>$source_field:</strong> " . htmlspecialchars($value);
      }
    }

    $html = implode('<br>', $text_parts);

    $elements[] = [
      '#type' => 'markup',
      '#markup' => '<div class="embedding-preview">' . $html . '</div>',
    ];

    return $elements;
  }
}
