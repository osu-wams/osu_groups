<?php

namespace Drupal\osu_groups_basic_group;

use Drupal\group\Entity\Group;

/**
 * Provides helper functions to get data about basic groups.
 */
class OsuGroupsBasicGroupHandler {

  /**
   * Get the Group Landing Page for given group.
   *
   * @param \Drupal\group\Entity\Group $group
   *   The Group to look at.
   *
   * @return \Drupal\node\Entity\Node|null
   *   The Node representing the lading page or null.
   */
  public function getGroupLandingNode(Group $group) {
    $group_landing_node_list = $group->get('field_group_landing_page');
    if (count($group_landing_node_list) > 0) {
      $group_landing_node = $group_landing_node_list
        ->first()
        ->get('entity')
        ->getTarget()
        ->getValue();
      return $group_landing_node;
    }
    else {
      return NULL;
    }
  }

}
