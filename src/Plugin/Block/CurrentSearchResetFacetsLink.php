<?php

/**
 * @file
 * Contains \Drupal\facets_current_search_block\Plugin\Block\CurrentSearchResetLink.
 */

namespace Drupal\facets_current_search_block\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Displays the facet reset link for the current search.
 *
 * @Block(
 *   id = "facets_current_search_block_reset_facets_link",
 *   admin_label =  @Translation("Current Search: Reset Facets link"),
 *   category = @Translation("Facets"),
 * )
 */
class CurrentSearchResetFacetsLink extends CurrentSearchBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'link_text' => 'Reset',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $this->configuration['link_text'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['link_text'] = $form_state->getValue('link_text');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $facet_source = $this->facetSourcePluginManager->createInstance($this->configuration['source_id']);
    $facets = $this->facetsManager->getFacetsByFacetSourceId($this->configuration['source_id']);
    $query = $this->request->query->all();

    // Get all the query keys for the facets.
    $facet_keys = array_map(function($facet) {
      $facet_source_config = $facet->getFacetSourceConfig();
      return $facet_source_config->getFilterKey() ?: 'f';
    }, $facets);

    // Remove all the facets based on their query keys.
    $reset = FALSE;
    foreach ($query as $key => $value) {
      if (in_array($key, $facet_keys) !== FALSE) {
        unset($query[$key]);
        $reset = TRUE;
      }
    }

    $reset_url = Url::fromUserInput($facet_source->getPath(), [
      'query' => $query,
    ]);

    $build = [];

    if ($reset) {
      $build['reset_link'] = [
        '#type' => 'link',
        '#title' => $this->t($this->configuration['link_text']),
        '#cache' => [
          'contexts' => ['languages:language_interface'],
        ],
        '#url' => $reset_url,
        '#attributes' => ['class' => ['facets-current-search--reset-link']],
      ];
    }

    return $build;
  }

}
