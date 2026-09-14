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
   * Update pages added to a group not in a menu with
   *   "/[node:group:url:path]/[node:title]"
   *
   * Update pages that are in the Group menu and are the top level link with
   *   "/[node:group:url:path]/[node:title]"
   */
  #[Hook('pathauto_pattern_alter')]
  public function pathautoPatternAlter(PathautoPatternInterface $pattern, array $context) {
    if ($context['module'] === 'node' && ($context['op'] === 'update' || $context['op'] === 'bulkupdate')) {
      $node = $context['data']['node'];
      $group_content = $this->osuGroupsHandler->getGroupContentFromNode($node);

      if ($group_content) {
        $menu_links = $this->menuLinkManager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

        if (empty($menu_links)) {
          $pattern->setPattern('/[node:group:url:path]/[node:title]');
        }
        else {
          $menu_link = reset($menu_links);
          $parent = $menu_link->getParent();

          if (empty($parent)) {
            $pattern->setPattern('/[node:group:url:path]/[node:title]');
          }
        }
      }
    }
  }

}
