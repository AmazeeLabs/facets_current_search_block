<?php

/**
 * @file
 * Contains \Drupal\facets_current_search_block\Plugin\Block\CurrentSearchFulltextSearch.
 */

namespace Drupal\facets_current_search_block\Plugin\Block;

use Drupal\Core\Url;
use Drupal\views\Entity\View;

/**
 * Displays current search key and a reset link for them.
 *
 * @Block(
 *   id = "facets_current_search_block_fulltext_search",
 *   admin_label =  @Translation("Current Search: Fulltext search"),
 *   category = @Translation("Facets"),
 * )
 */
class CurrentSearchFulltextSearch extends CurrentSearchBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $plugin_type = explode(':', $this->configuration['source_id'])[0];
    $view_name = explode('_', $this->configuration['source_id'])[4];
    if ($plugin_type == 'search_api') {
      /** @var \Drupal\views\Entity\View $view */
      if ($view = View::load($view_name)) {
        $view_executable = $view->getExecutable();
        foreach ($view_executable->getHandlers('filter') as $filter_id => $filter) {
          if ($filter['field'] == 'search_api_fulltext' && $filter['exposed']) {
            $input = $view_executable->getExposedInput();
            if (isset($input[$filter['expose']['identifier']]) && trim($input[$filter['expose']['identifier']]) !== '') {
              $query = \Drupal::request()->query->all();
              unset($query[$filter['expose']['identifier']]);
              $build[$filter_id] = [
                '#theme' => 'facets_current_search_item',
                '#label' => $input[$filter['expose']['identifier']],
                '#url' => Url::fromRoute('<current>', [], ['query' => $query]),
              ];
            }
          }
        }
      }
    }

    return $build;
  }

}
