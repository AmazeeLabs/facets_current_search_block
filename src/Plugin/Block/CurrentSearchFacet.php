<?php

/**
 * @file
 * Contains \Drupal\facets_current_search_block\Plugin\Block\CurrentSearchFacet.
 */

namespace Drupal\facets_current_search_block\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\Entity\Facet;

/**
 * Displays a value and a reset link for a single facet (if it's active).
 *
 * @Block(
 *   id = "facets_current_search_block_facet",
 *   admin_label =  @Translation("Current Search: Facet"),
 *   category = @Translation("Facets"),
 * )
 */
class CurrentSearchFacet extends CurrentSearchBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'facet_id' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $facet_manager = \Drupal::service('facets.manager');
    $all_facets = $facet_manager->getEnabledFacets();
    foreach (array_keys($form['source_id']['#options']) as $source_id) {
      $options = [
        '' => $this->t('- Select -'),
      ];
      foreach ($all_facets as $facet) {
        if ($facet->getFacetSourceId() == $source_id) {
          $options[$facet->id()] = $facet->label();
        }
      }
      $form['facet_id:' . $source_id] = [
        '#type' => 'select',
        '#title' => $this->t('Facet'),
        '#options' => $options,
        '#default_value' => isset($options[$this->configuration['facet_id']]) ? $this->configuration['facet_id'] : NULL,
        '#states' => [
          'visible' => [
            'select[name="settings[source_id]"]' => ['value' => $source_id],
          ],
          'required' => [
            'select[name="settings[source_id]"]' => ['value' => $source_id],
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['facet_id'] = $form_state->getValue('facet_id:' . $this->configuration['source_id']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($facet = Facet::load($this->configuration['facet_id'])) {
      /** @var \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager */
      $facet_manager = \Drupal::service('facets.manager');
      $facet = $facet_manager->returnProcessedFacet($facet);
      foreach ($facet->getResults() as $index => $result) {
        if ($result->isActive()) {
          $build[$facet->id()][$index] = [
            '#theme' => 'facets_current_search_item',
            '#label' => $result->getDisplayValue(),
            '#url' => $result->getUrl(),
          ];
        }
      }
    }

    return $build;
  }

}
