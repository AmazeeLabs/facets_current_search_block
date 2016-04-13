<?php

/**
 * @file
 * Contains \Drupal\facets_current_search_block\Plugin\Block\CurrentSearchBase.
 */

namespace Drupal\facets_current_search_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Current Search blocks.
 */
abstract class CurrentSearchBase extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'source_id' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    /** @var \Drupal\Component\Plugin\PluginManagerInterface $facet_source_plugin_manager */
    $facet_source_plugin_manager = \Drupal::service('plugin.manager.facets.facet_source');
    $facet_sources = $facet_source_plugin_manager->getDefinitions();
    $options = [];
    foreach ($facet_sources as $facet_source) {
      $options[$facet_source['id']] = $facet_source['label'];
    }

    $form['source_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Source'),
      '#options' => $options,
      '#default_value' => $this->configuration['source_id'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['source_id'] = $form_state->getValue('source_id');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    /* @see \Drupal\facets\Plugin\Block\FacetBlock::getCacheMaxAge() */
    return 0;
  }

}
