<?php

declare(strict_types=1);

namespace Drupal\osu_groups_basic_group\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\osu_groups\OsuGroupsHandler;

/**
 * OSU Groups Basic Group Theme Hooks.
 */
class OsuGroupsBasicGroupThemeHooks {

  use StringTranslationTrait;

  public function __construct(
    protected readonly RouteMatchInterface $routeMatch,
    protected readonly OsuGroupsHandler $osuGroupsHandler,
    protected readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_menu__group_menu')]
  public function preprocessGroupMenu(&$variables) {
    // Add an ID to the menu ul.
    $variables['attributes']['id'] = 'group-content-menu';
  }

  /**
   * Implements hook_preprocess_html().
   */
  #[Hook('preprocess_html')]
  public function preprocessHtml(&$variables) {
    $node = $this->routeMatch->getParameter('node');

    if ($node) {
      $group_content = $this->osuGroupsHandler->getGroupContentFromNode($node);
      $group_node_auto_title = $this->configFactory
        ->getEditable('group.settings')->get('osu_groups_page_title');

      if ($group_content && $group_node_auto_title) {
        /** @var \Drupal\group\Entity\Group $group */
        $group = $group_content->getGroup();
        // On Group Content Pages insert Group name after node name.
        $group_name = $this->osuGroupsHandler->getGroupnameFromGroup($group);
        $page_title_array = explode(' | ', (string) $variables['head_title']['title']);
        array_splice($page_title_array, 1, 0, $group_name);
        $variables['head_title']['title'] = implode(' | ', $page_title_array);
      }
    }
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_page_title')]
  public function preprocessPageTitle(&$variables) {
    // If we are on a group entity and on the display of it, hide the title.
    if ($this->routeMatch->getParameter('group')
      && $this->routeMatch->getRouteName() === 'entity.group.canonical') {
      $variables['title_attributes']['class'][] = 'hidden';
    }
  }

}
