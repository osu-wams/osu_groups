<?php

declare(strict_types=1);

namespace Drupal\osu_groups;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

/**
 * Provides helper functions to get data about groups.
 */
class OsuGroupsHandler {

  /**
   * The Entity Type manager Service.
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Creates a new OsuGroupsHandler.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Type manager Service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get the group name for the given group.
   *
   * @param \Drupal\group\Entity\Group $group
   *   The Group to look interrogate.
   *
   * @return string
   *   The String representing the Groups Name.
   */
  public function getGroupnameFromGroup(Group $group): string {
    return $group->get('label')->first()->getValue()['value'];
  }

  /**
   * Get the Group name for the given node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Group to interrogate.
   *
   * @return string|null
   *   The Group Name or null.
   */
  public function getGroupNameFromNode(Node $node) {
    /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_relationship');
    $group_relationship = $storage->loadByEntity($node);

    if ($group_relationship) {
      $group_relationship = reset($group_relationship);
      $group = $group_relationship->getGroup();

      return $group->get('label')->first()->getValue()['value'];
    }

    return NULL;
  }

  /**
   * Get the Group Content entity for the node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to interrogate.
   *
   * @return \Drupal\group\Entity\GroupRelationshipInterface|null
   *   The Group Content Entity or null.
   */
  public function getGroupRelationshipFromNode(Node $node) {
    /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_relationship');
    $group_relationship = $storage->loadByEntity($node);

    if ($group_relationship) {
      return reset($group_relationship);
    }

    return NULL;
  }

}
