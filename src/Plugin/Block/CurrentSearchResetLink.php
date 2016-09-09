<?php

/**
 * @file
 * Contains \Drupal\facets_current_search_block\Plugin\Block\CurrentSearchResetLink.
 */

namespace Drupal\facets_current_search_block\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Displays the full reset link for the current search.
 *
 * @Block(
 *   id = "facets_current_search_block_reset_link",
 *   admin_label =  @Translation("Current Search: Reset link"),
 *   category = @Translation("Facets"),
 * )
 */
class CurrentSearchResetLink extends CurrentSearchBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // @todo: this config setting should be translatable.
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
    $build = [];

    /** @var \Drupal\facets\FacetSource\FacetSourcePluginManager $facet_source_manager */
    $facet_source_manager = \Drupal::service('plugin.manager.facets.facet_source');
    /** @var \Drupal\facets\FacetSource\FacetSourcePluginInterface $facet_source */
    $facet_source = $facet_source_manager->createInstance($this->configuration['source_id']);

    $reset_url = Url::fromUserInput($facet_source->getPath());
    $request = \Drupal::request();
    $current_url = $request->getSchemeAndHttpHost() . $request->getRequestUri();

    // Ignore "page" and all the empty query parameters.
    $parsed = parse_url($current_url);
    parse_str(isset($parsed['query']) ? $parsed['query'] : '', $query);
    foreach ($query as $key => $value) {
      if ($key == 'page' || $value === '') {
        unset($query[$key]);
      }
    }
    if (count($query)) {
      $parsed['query'] = http_build_query($query);
    }
    else {
      unset($parsed['query']);
    }
    $current_url = $this->buildUrl($parsed);

    if ($current_url != $reset_url->setAbsolute()->toString()) {
      $build = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => ['class' => ['facets-current-search--reset-link']],
      ];
      $build['reset_link'] = [
        '#type' => 'link',
        '#title' => $this->configuration['link_text'],
        '#url' => $reset_url,
      ];
    }

    return $build;
  }

  /**
   * Builds a URL from the parse_url() result.
   *
   * Taken from http://php.net/manual/en/function.parse-url.php#106731
   *
   * @param array $parsed_url
   *   The result of parse_url().
   *
   * @return string
   */
  public function buildUrl($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
  }

}
