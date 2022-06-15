<?php

namespace Drupal\osu_groups_basic_group;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\Element\RenderCallbackInterface;

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
    // Ensures Block will be cached based on URL path only.
    CacheableMetadata::createFromRenderArray($build)
      ->addCacheContexts(['url.path'])
      ->applyTo($build);

    /** @var \Drupal\osu_groups\OsuGroupsHandler $osu_groups */
    $osu_groups = \Drupal::service('osu_groups.group_handler');

    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      $group_content = $osu_groups->getGroupContentFromNode($node);
      if ($group_content) {
        $group_name = $osu_groups->getGroupNameFromNode($node);
        $group = $group_content->getGroup();
        // Set the group name in the site branding block.
        $build['content']['group_name']['#markup'] = $group_name;
        $group_link = $group->toUrl()->toString();
        // Set the path for the site branding block.
        $build['content']['group_path']['#uri'] = $group_link;

      }
    }
    // For Group Entities Only.
    elseif ($group = \Drupal::routeMatch()->getParameter('group')) {
      $group_name = $osu_groups->getGroupnameFromGroup($group);
      // Set the group name in the site branding block.
      $build['content']['group_name']['#markup'] = $group_name;
      $group_link = $group->toUrl()->toString();
      // Set the path for the site branding block.
      $build['content']['group_path']['#uri'] = $group_link;
    }
    return $build;
  }

}
