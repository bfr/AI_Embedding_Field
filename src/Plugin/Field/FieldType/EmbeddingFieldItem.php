<?php

namespace Drupal\ai_embedding_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'ai_embedding_field' field type.
 *
 * @FieldType(
 *   id = "ai_embedding_field",
 *   label = @Translation("Embedding field"),
 *   description = @Translation("Stores an OpenAI-style embedding field as JSON."),
 *   default_widget = "string_textfield",
 *   default_formatter = "string"
 * )
 */
class EmbeddingFieldItem extends FieldItemBase {

  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Embedding field (JSON or gzipped JSON)'));
    return $properties;
  }

  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'text',
          'size' => 'big',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  public static function defaultFieldSettings() {
    return [
      'gzip_embedding' => FALSE,
    ] + parent::defaultFieldSettings();
  }


  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $element = parent::fieldSettingsForm($form, $form_state); 

    // Check AI embedding provider configuration
    try {
      $provider_manager = \Drupal::service('ai.provider');
      $sets = $provider_manager->getDefaultProviderForOperationType('embeddings');
      if (empty($sets['provider_id']) || empty($sets['model_id'])) {
        \Drupal::messenger()->addError($this->t('No embedding provider or model configured. Please configure it at <a href=":url">AI settings</a>.', [
          ':url' => Url::fromRoute('ai.settings')->toString(),
        ]));
      }
    } catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('AI module not properly installed or the service is unavailable.'));
    }

    $element['gzip_embedding'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Store embedding as gzipped JSON'),
      '#default_value' => $settings['gzip_embedding'],
      '#description' => $this->t('Enable to save space in the database.'),
    ];

    return $element;
  }

  public static function fieldSettingsFormSubmit(array $form, FormStateInterface $form_state) {
    return [
      'gzip_embedding' => (bool) $form_state->getValue('gzip_embedding'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }
}
