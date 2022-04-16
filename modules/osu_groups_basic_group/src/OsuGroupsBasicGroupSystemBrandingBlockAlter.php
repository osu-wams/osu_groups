<?php

namespace Drupal\osu_groups_basic_group;

use Drupal;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\Element\RenderCallbackInterface;
use Drupal\node\Entity\Node;
use Drupal\osu_groups\OsuGroupsHandler;

/**
 * Provides a trusted callback to alter the system branding block.
 *
 * @see osu_groups_basic_group_block_view_system_branding_block_alter()
 */
class OsuGroupsBasicGroupSystemBrandingBlockAlter implements RenderCallbackInterface {

  /**
   * #pre_render callback: Sets site name if node is in a group.
   */
  public static function preRender($build) {
    $current_path = Drupal::service('path.current')->getPath();
    $path = Drupal::service('path_alias.manager')
      ->getPathByAlias($current_path);
    CacheableMetadata::createFromRenderArray($build)
      ->addCacheContexts(['url.path'])
      ->applyTo($build);
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = Node::load($matches[1]);
      $osu_groups = new OsuGroupsHandler();
      $osu_groups_basic_group = new OsuGroupsBasicGroupHandler();
      $group_content = $osu_groups->getGroupContentFromNode($node);
      // Ensures Block will be cached based on URL path only.
      if ($group_content) {
        $group_name = $osu_groups->getGroupNameFromNode($node);
        $group_landing_page = $osu_groups_basic_group->getGroupLandingNode($group_content->getGroup());
        $group_landing_page->toUrl();
        $group_landing_page_path = $group_landing_page->toUrl()->toString();
        $build['content']['site_name']['#markup'] = $group_name;
        $build['content']['site_path']['#uri'] = $group_landing_page_path;
      }
    }
    return $build;
  }

}