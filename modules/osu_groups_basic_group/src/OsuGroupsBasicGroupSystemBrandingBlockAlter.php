<?php

namespace Drupal\osu_groups_basic_group;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\Element\RenderCallbackInterface;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

/**
 * Provides a trusted callback to alter the system branding block.
 *
 * @see osu_groups_basic_group_block_view_system_branding_block_alter()
 */
class OsuGroupsBasicGroupSystemBrandingBlockAlter implements RenderCallbackInterface {

  /**
   * Pre Render Callback Sets site name if node is in a group.
   */
  public static function preRender($build) {
    $current_path = \Drupal::service('path.current')->getPath();
    $path = \Drupal::service('path_alias.manager')
      ->getPathByAlias($current_path);
    // Ensures Block will be cached based on URL path only.
    CacheableMetadata::createFromRenderArray($build)
      ->addCacheContexts(['url.path'])
      ->applyTo($build);

    /** @var \Drupal\osu_groups\OsuGroupsHandler $osu_groups */
    $osu_groups = \Drupal::service('osu_groups.group_handler');
    /** @var \Drupal\osu_groups_basic_group\OsuGroupsBasicGroupHandler $osu_groups_basic_group */
    $osu_groups_basic_group = \Drupal::service('osu_groups_basic_group.group_handler');

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = Node::load($matches[1]);
      $group_content = $osu_groups->getGroupContentFromNode($node);
      if ($group_content) {
        $group_name = $osu_groups->getGroupNameFromNode($node);
        # Set the group name in the site branding block.
        $build['content']['site_name']['#markup'] = $group_name;
        $group_landing_page = $osu_groups_basic_group->getGroupLandingNode($group_content->getGroup());
        if (!is_null($group_landing_page)) {
          $group_landing_page->toUrl();
          $group_landing_page_path = $group_landing_page->toUrl()->toString();
          # Set the path for the site branding block.
          $build['content']['site_path']['#uri'] = $group_landing_page_path;
        }
      }
    }
    elseif (preg_match('/group\/(\d+)/', $path, $matches)) {
      $group = Group::load($matches[1]);
      $group_name = $osu_groups->getGroupnameFromGroup($group);
      # Set the group name in the site branding block.
      $build['content']['site_name']['#markup'] = $group_name;
      $group_landing_page = $osu_groups_basic_group->getGroupLandingNode($group);
      if (!is_null($group_landing_page)) {
        $group_landing_page->toUrl();
        $group_landing_page_path = $group_landing_page->toUrl()->toString();
        # Set the path for the site branding block.
        $build['content']['site_path']['#uri'] = $group_landing_page_path;
      }
    }
    return $build;
  }

}
