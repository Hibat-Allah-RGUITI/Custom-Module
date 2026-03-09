<?php

declare(strict_types=1);

namespace Drupal\anytown\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[Block(
  id: "selected_node_block",
  admin_label: new TranslatableMarkup("Selected Node Block")
)]
class SelectedNodeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $selected_nid = \Drupal::state()->get('anytown_selected_node');

    if (empty($selected_nid)) {
      return [
        '#markup' => $this->t('No node selected yet.'),
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    /** @var \Drupal\node\NodeInterface|null $selected_node */
    $selected_node = $node_storage->load($selected_nid);

    if (!$selected_node) {
      return [
        '#markup' => $this->t('Selected node not found.'),
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }

    $query = \Drupal::entityQuery('node')
      ->condition('type', $selected_node->bundle())
      ->condition('nid', $selected_node->id(), '<>')
      ->accessCheck(TRUE);

    $nodes = $query->execute();
    $other_nodes = $node_storage->loadMultiple($nodes);

    $titles = [];
    foreach ($other_nodes as $node) {
      $titles[] = $node->getTitle();
    }

    return [
      '#theme' => 'anytown_selected_node',
      '#selected_title' => $selected_node->getTitle(),
      '#other_titles' => $titles,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }
}