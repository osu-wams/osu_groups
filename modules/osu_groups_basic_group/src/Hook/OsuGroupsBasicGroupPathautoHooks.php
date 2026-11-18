<?php

declare(strict_types=1);

namespace Drupal\osu_groups_basic_group\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\osu_groups\OsuGroupsHandler;
use Drupal\pathauto\PathautoPatternInterface;

/**
 * Basic Group Pathauto Hooks.
 */
class OsuGroupsBasicGroupPathautoHooks {

  public function __construct(
    private readonly OsuGroupsHandler $osuGroupsHandler,
    private readonly MenuLinkManagerInterface $menuLinkManager,
  ) {}

  /**
   * Implements hook_pathauto_pattern_alter().
   *
   * Using Patch from https://www.drupal.org/project/group/issues/2774827
   *
   * Group content that is not in a menu or is a top-level menu item uses
   * the Group path. Nested menu items retain their normal selected pattern.
   */
  #[Hook('pathauto_pattern_alter')]
  public function pathautoPatternAlter(PathautoPatternInterface $pattern, array $context): void {
    if ($context['module'] === 'node' && ($context['op'] === 'update' || $context['op'] === 'bulkupdate')) {
      $node = $context['data']['node'];
      $group_content = $this->osuGroupsHandler->getGroupContentFromNode($node);

      if (!$group_content) {
        return;
      }

      $menu_links = $this->menuLinkManager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

      $menu_link = reset($menu_links);

      if ($menu_link === FALSE || $menu_link->getParent() === '') {
        $pattern->setPattern('/[node:group:url:path]/[node:title]');
      }
    }
  }

}
