<?php

declare(strict_types=1);

namespace Drupal\osu_groups_basic_group\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 *
 */
class OsuGroupsBasicGroupFormHooks {

  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_group_settings_alter')]
  public function groupSettingsFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    $form['osu_groups_page_title'] = [
      '#type' => 'checkbox',
      '#title' => t('Automatically add the Group name to the Page title'),
      '#default_value' => $this->configFactory->get('group.settings')->get('osu_groups_page_title'),
      '#description' => t('Automatically have the group name added to the Page title.'),
    ];
    // Add a custom submit handler to save the setting.
    $form['#submit'][] = 'osu_groups_basic_group_group_settings_submit';

    return $form;
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_node_form_alter')]
  public function nodeFormAlter(&$form, FormStateInterface $form_state) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $form_state->getFormObject()->getEntity();

    // If node is new and created in group context
    // OR node is a group page and does not have a normal menu entry.
    if (($form_state->get('group') && $node->isNew())
      || (!menu_ui_get_menu_link_defaults($node)['id'] && osu_groups_basic_group_is_group())) {
      $form['menu']['link']['menu_parent']['#default_value'] = 'Group Menu:';
    }
  }

}
